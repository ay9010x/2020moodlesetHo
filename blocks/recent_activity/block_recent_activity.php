<?php



require_once($CFG->dirroot.'/course/lib.php');


class block_recent_activity extends block_base {

    
    protected $timestart = null;

    
    function init() {
        $this->title = get_string('pluginname', 'block_recent_activity');
    }

    
    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $renderer = $this->page->get_renderer('block_recent_activity');
        $this->content->text = $renderer->recent_activity($this->page->course,
                $this->get_timestart(),
                $this->get_recent_enrolments(),
                $this->get_structural_changes(),
                $this->get_modules_recent_activity());

        return $this->content;
    }

    
    protected function get_timestart() {
        global $USER;
        if ($this->timestart === null) {
            $this->timestart = round(time() - COURSE_MAX_RECENT_PERIOD, -2); 
            if (!isguestuser()) {
                if (!empty($USER->lastcourseaccess[$this->page->course->id])) {
                    if ($USER->lastcourseaccess[$this->page->course->id] > $this->timestart) {
                        $this->timestart = $USER->lastcourseaccess[$this->page->course->id];
                    }
                }
            }
        }
        return $this->timestart;
    }

    
    protected function get_recent_enrolments() {
        return array();
    }

    
    protected function get_structural_changes() {
        global $DB;
        $course = $this->page->course;
        $context = context_course::instance($course->id);
        $canviewdeleted = has_capability('block/recent_activity:viewdeletemodule', $context);
        $canviewupdated = has_capability('block/recent_activity:viewaddupdatemodule', $context);
        if (!$canviewdeleted && !$canviewupdated) {
            return;
        }

        $timestart = $this->get_timestart();
        $changelist = array();
                        $sql = "SELECT
                    cmid, MIN(action) AS minaction, MAX(action) AS maxaction, MAX(modname) AS modname
                FROM {block_recent_activity}
                WHERE timecreated > ? AND courseid = ?
                GROUP BY cmid
                ORDER BY MAX(timecreated) ASC";
        $params = array($timestart, $course->id);
        $logs = $DB->get_records_sql($sql, $params);
        if (isset($logs[0])) {
                        self::migrate_logs($course);
            $logs = $DB->get_records_sql($sql, $params);
        }
        if ($logs) {
            $modinfo = get_fast_modinfo($course);
            foreach ($logs as $log) {
                                $wasdeleted = ($log->maxaction == block_recent_activity_observer::CM_DELETED);
                $wascreated = ($log->minaction == block_recent_activity_observer::CM_CREATED);

                if ($wasdeleted && $wascreated) {
                                        continue;
                } else if ($wasdeleted && $canviewdeleted) {
                    if (plugin_supports('mod', $log->modname, FEATURE_NO_VIEW_LINK, false)) {
                                                                        continue;
                    }
                                        $modnames = get_module_types_names();
                    $changelist[$log->cmid] = array('action' => 'delete mod',
                        'module' => (object)array(
                            'modname' => $log->modname,
                            'modfullname' => isset($modnames[$log->modname]) ? $modnames[$log->modname] : $log->modname
                         ));

                } else if (!$wasdeleted && isset($modinfo->cms[$log->cmid]) && $canviewupdated) {
                                                            $cm = $modinfo->cms[$log->cmid];
                    if ($cm->has_view() && $cm->uservisible) {
                        $changelist[$log->cmid] = array(
                            'action' => $wascreated ? 'add mod' : 'update mod',
                            'module' => $cm
                        );
                    }
                }
            }
        }
        return $changelist;
    }

    
    protected function get_modules_recent_activity() {
        $context = context_course::instance($this->page->course->id);
        $viewfullnames = has_capability('moodle/site:viewfullnames', $context);
        $hascontent = false;

        $modinfo = get_fast_modinfo($this->page->course);
        $usedmodules = $modinfo->get_used_module_names();
        $recentactivity = array();
        foreach ($usedmodules as $modname => $modfullname) {
                        ob_start();
            $hascontent = component_callback('mod_'. $modname, 'print_recent_activity',
                    array($this->page->course, $viewfullnames, $this->get_timestart()), false);
            if ($hascontent) {
                $recentactivity[$modname] = ob_get_contents();
            }
            ob_end_clean();
        }
        return $recentactivity;
    }

    
    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }

    
    public function cron() {
        global $DB;
                $DB->delete_records_select('block_recent_activity', 'timecreated < ?',
                array(time() - COURSE_MAX_RECENT_PERIOD));
    }

    
    protected static function migrate_logs($course) {
        global $DB;
        if (!$logstarted = $DB->get_record('block_recent_activity',
                array('courseid' => $course->id, 'cmid' => 0),
                'id, timecreated')) {
            return;
        }
        $DB->delete_records('block_recent_activity', array('id' => $logstarted->id));
        try {
            $logs = $DB->get_records_select('log',
                    "time > ? AND time < ? AND course = ? AND
                        module = 'course' AND
                        (action = 'add mod' OR action = 'update mod' OR action = 'delete mod')",
                    array(time()-COURSE_MAX_RECENT_PERIOD, $logstarted->timecreated, $course->id),
                    'id ASC', 'id, time, userid, cmid, action, info');
        } catch (Exception $e) {
                        return;
        }
        if (!$logs) {
            return;
        }
        $modinfo = get_fast_modinfo($course);
        $entries = array();
        foreach ($logs as $log) {
            $info = explode(' ', $log->info);
            if (count($info) != 2) {
                continue;
            }
            $modname = $info[0];
            $instanceid = $info[1];
            $entry = array('courseid' => $course->id, 'userid' => $log->userid,
                'timecreated' => $log->time, 'modname' => $modname);
            if ($log->action == 'delete mod') {
                if (!$log->cmid) {
                    continue;
                }
                $entry['action'] = 2;
                $entry['cmid'] = $log->cmid;
            } else {
                if (!isset($modinfo->instances[$modname][$instanceid])) {
                    continue;
                }
                if ($log->action == 'add mod') {
                    $entry['action'] = 0;
                } else {
                    $entry['action'] = 1;
                }
                $entry['cmid'] = $modinfo->instances[$modname][$instanceid]->id;
            }
            $entries[] = $entry;
        }
        $DB->insert_records('block_recent_activity', $entries);
    }
}

