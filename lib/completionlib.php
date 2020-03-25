<?php



defined('MOODLE_INTERNAL') || die();


require_once $CFG->dirroot.'/completion/completion_aggregation.php';
require_once $CFG->dirroot.'/completion/criteria/completion_criteria.php';
require_once $CFG->dirroot.'/completion/completion_completion.php';
require_once $CFG->dirroot.'/completion/completion_criteria_completion.php';



define('COMPLETION_ENABLED', 1);

define('COMPLETION_DISABLED', 0);


define('COMPLETION_TRACKING_NONE', 0);


define('COMPLETION_TRACKING_MANUAL', 1);

define('COMPLETION_TRACKING_AUTOMATIC', 2);


define('COMPLETION_INCOMPLETE', 0);

define('COMPLETION_COMPLETE', 1);

define('COMPLETION_COMPLETE_PASS', 2);

define('COMPLETION_COMPLETE_FAIL', 3);


define('COMPLETION_UNKNOWN', -1);

define('COMPLETION_GRADECHANGE', -2);


define('COMPLETION_VIEW_REQUIRED', 1);

define('COMPLETION_VIEW_NOT_REQUIRED', 0);


define('COMPLETION_VIEWED', 1);

define('COMPLETION_NOT_VIEWED', 0);


define('COMPLETION_OR', false);

define('COMPLETION_AND', true);


define('COMPLETION_AGGREGATION_ALL', 1);

define('COMPLETION_AGGREGATION_ANY', 2);



function completion_can_view_data($userid, $course = null) {
    global $USER;

    if (!isloggedin()) {
        return false;
    }

    if (!is_object($course)) {
        $cid = $course;
        $course = new stdClass();
        $course->id = $cid;
    }

        if ($course->id == SITEID) {
        $course = null;
    }

        if ($course) {
        $cinfo = new completion_info($course);
        if (!$cinfo->is_enabled()) {
            return false;
        }
    } else {
        if (!completion_info::is_enabled_for_site()) {
            return false;
        }
    }

        if ($USER->id == $userid) {
        return true;
    }

        $personalcontext = context_user::instance($userid);

    if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        return true;
    } elseif (has_capability('report/completion:view', $personalcontext)) {
        return true;
    }

    if ($course->id) {
        $coursecontext = context_course::instance($course->id);
    } else {
        $coursecontext = context_system::instance();
    }

    if (has_capability('report/completion:view', $coursecontext)) {
        return true;
    }

    return false;
}



class completion_info {

    
    private $course;

    
    public $course_id;

    
    private $criteria;

    
    public static function get_aggregation_methods() {
        return array(
            COMPLETION_AGGREGATION_ALL => get_string('all'),
            COMPLETION_AGGREGATION_ANY => get_string('any', 'completion'),
        );
    }

    
    public function __construct($course) {
        $this->course = $course;
        $this->course_id = $course->id;
    }

    
    public static function is_enabled_for_site() {
        global $CFG;
        return !empty($CFG->enablecompletion);
    }

    
    public function is_enabled($cm = null) {
        global $CFG, $DB;

                if (!isset($CFG->enablecompletion) || $CFG->enablecompletion == COMPLETION_DISABLED) {
            return COMPLETION_DISABLED;
        }

                if (!isset($this->course->enablecompletion)) {
            $this->course = get_course($this->course_id);
        }

                if ($this->course->enablecompletion == COMPLETION_DISABLED) {
            return COMPLETION_DISABLED;
        }

                if (!$cm) {
            return COMPLETION_ENABLED;
        }

                return $cm->completion;
    }

    
    public function print_help_icon() {
        print $this->display_help_icon();
    }

    
    public function display_help_icon() {
        global $PAGE, $OUTPUT;
        $result = '';
        if ($this->is_enabled() && !$PAGE->user_is_editing() && isloggedin() && !isguestuser()) {
            $result .= html_writer::tag('div', get_string('yourprogress','completion') .
                    $OUTPUT->help_icon('completionicons', 'completion'), array('id' => 'completionprogressid',
                    'class' => 'completionprogress'));
        }
        return $result;
    }

    
    public function get_completion($user_id, $criteriatype) {
        $completions = $this->get_completions($user_id, $criteriatype);

        if (empty($completions)) {
            return false;
        } elseif (count($completions) > 1) {
            print_error('multipleselfcompletioncriteria', 'completion');
        }

        return $completions[0];
    }

    
    public function get_completions($user_id, $criteriatype = null) {
        $criterion = $this->get_criteria($criteriatype);

        $completions = array();

        foreach ($criterion as $criteria) {
            $params = array(
                'course'        => $this->course_id,
                'userid'        => $user_id,
                'criteriaid'    => $criteria->id
            );

            $completion = new completion_criteria_completion($params);
            $completion->attach_criteria($criteria);

            $completions[] = $completion;
        }

        return $completions;
    }

    
    public function get_user_completion($user_id, $criteria) {
        $params = array(
            'course'        => $this->course_id,
            'userid'        => $user_id,
            'criteriaid'    => $criteria->id,
        );

        $completion = new completion_criteria_completion($params);
        return $completion;
    }

    
    public function has_criteria() {
        $criteria = $this->get_criteria();

        return (bool) count($criteria);
    }

    
    public function get_criteria($criteriatype = null) {

                if (!is_array($this->criteria)) {
            global $DB;

            $params = array(
                'course'    => $this->course->id
            );

                        $records = (array)$DB->get_records('course_completion_criteria', $params);

                        $this->criteria = array();
            foreach ($records as $record) {
                $this->criteria[$record->id] = completion_criteria::factory((array)$record);
            }
        }

                if ($criteriatype === null) {
            return $this->criteria;
        }

                $criteria = array();
        foreach ($this->criteria as $criterion) {

            if ($criterion->criteriatype != $criteriatype) {
                continue;
            }

            $criteria[$criterion->id] = $criterion;
        }

        return $criteria;
    }

    
    public function get_aggregation_method($criteriatype = null) {
        $params = array(
            'course'        => $this->course_id,
            'criteriatype'  => $criteriatype
        );

        $aggregation = new completion_aggregation($params);

        if (!$aggregation->id) {
            $aggregation->method = COMPLETION_AGGREGATION_ALL;
        }

        return $aggregation->method;
    }

    
    public function get_incomplete_criteria() {
        throw new coding_exception('completion_info->get_incomplete_criteria() is removed.');
    }

    
    public function clear_criteria() {
        global $DB;
        $DB->delete_records('course_completion_criteria', array('course' => $this->course_id));
        $DB->delete_records('course_completion_aggr_methd', array('course' => $this->course_id));

        $this->delete_course_completion_data();
    }

    
    public function is_course_complete($user_id) {
        $params = array(
            'userid'    => $user_id,
            'course'  => $this->course_id
        );

        $ccompletion = new completion_completion($params);
        return $ccompletion->is_complete();
    }

    
    public function update_state($cm, $possibleresult=COMPLETION_UNKNOWN, $userid=0) {
        global $USER;

                if (!$this->is_enabled($cm)) {
            return;
        }

                                $current = $this->get_data($cm, false, $userid);
        if ($possibleresult == $current->completionstate ||
            ($possibleresult == COMPLETION_COMPLETE &&
                ($current->completionstate == COMPLETION_COMPLETE_PASS ||
                $current->completionstate == COMPLETION_COMPLETE_FAIL))) {
            return;
        }

        if ($cm->completion == COMPLETION_TRACKING_MANUAL) {
                        switch($possibleresult) {
                case COMPLETION_COMPLETE:
                case COMPLETION_INCOMPLETE:
                    $newstate = $possibleresult;
                    break;
                default:
                    $this->internal_systemerror("Unexpected manual completion state for {$cm->id}: $possibleresult");
            }

        } else {
                        $newstate = $this->internal_get_state($cm, $userid, $current);
        }

                if ($newstate != $current->completionstate) {
            $current->completionstate = $newstate;
            $current->timemodified    = time();
            $this->internal_set_data($cm, $current);
        }
    }

    
    public function internal_get_state($cm, $userid, $current) {
        global $USER, $DB, $CFG;

                if (!$userid) {
            $userid = $USER->id;
        }

                if ($cm->completionview == COMPLETION_VIEW_REQUIRED &&
            $current->viewed == COMPLETION_NOT_VIEWED) {

            return COMPLETION_INCOMPLETE;
        }

                if (!isset($cm->modname)) {
            $cm->modname = $DB->get_field('modules', 'name', array('id'=>$cm->module));
        }

        $newstate = COMPLETION_COMPLETE;

                if (!is_null($cm->completiongradeitemnumber)) {
            require_once($CFG->libdir.'/gradelib.php');
            $item = grade_item::fetch(array('courseid'=>$cm->course, 'itemtype'=>'mod',
                'itemmodule'=>$cm->modname, 'iteminstance'=>$cm->instance,
                'itemnumber'=>$cm->completiongradeitemnumber));
            if ($item) {
                                $grades = grade_grade::fetch_users_grades($item, array($userid), false);
                if (empty($grades)) {
                                        return COMPLETION_INCOMPLETE;
                }
                if (count($grades) > 1) {
                    $this->internal_systemerror("Unexpected result: multiple grades for
                        item '{$item->id}', user '{$userid}'");
                }
                $newstate = self::internal_get_grade_state($item, reset($grades));
                if ($newstate == COMPLETION_INCOMPLETE) {
                    return COMPLETION_INCOMPLETE;
                }

            } else {
                $this->internal_systemerror("Cannot find grade item for '{$cm->modname}'
                    cm '{$cm->id}' matching number '{$cm->completiongradeitemnumber}'");
            }
        }

        if (plugin_supports('mod', $cm->modname, FEATURE_COMPLETION_HAS_RULES)) {
            $function = $cm->modname.'_get_completion_state';
            if (!function_exists($function)) {
                $this->internal_systemerror("Module {$cm->modname} claims to support
                    FEATURE_COMPLETION_HAS_RULES but does not have required
                    {$cm->modname}_get_completion_state function");
            }
            if (!$function($this->course, $cm, $userid, COMPLETION_AND)) {
                return COMPLETION_INCOMPLETE;
            }
        }

        return $newstate;

    }

    
    public function set_module_viewed($cm, $userid=0) {
        global $PAGE;
        if ($PAGE->headerprinted) {
            debugging('set_module_viewed must be called before header is printed',
                    DEBUG_DEVELOPER);
        }

                if ($cm->completionview == COMPLETION_VIEW_NOT_REQUIRED || !$this->is_enabled($cm)) {
            return;
        }

                $data = $this->get_data($cm, false, $userid);

                if ($data->viewed == COMPLETION_VIEWED) {
            return;
        }

                $data->viewed = COMPLETION_VIEWED;
        $this->internal_set_data($cm, $data);
        $this->update_state($cm, COMPLETION_COMPLETE, $userid);
    }

    
    public function count_user_data($cm) {
        global $DB;

        return $DB->get_field_sql("
    SELECT
        COUNT(1)
    FROM
        {course_modules_completion}
    WHERE
        coursemoduleid=? AND completionstate<>0", array($cm->id));
    }

    
    public function count_course_user_data($user_id = null) {
        global $DB;

        $sql = '
    SELECT
        COUNT(1)
    FROM
        {course_completion_crit_compl}
    WHERE
        course = ?
        ';

        $params = array($this->course_id);

                if ($user_id) {
            $sql .= ' AND userid = ?';
            $params[] = $user_id;
        }

        return $DB->get_field_sql($sql, $params);
    }

    
    public function is_course_locked() {
        return (bool) $this->count_course_user_data();
    }

    
    public function delete_course_completion_data() {
        global $DB;

        $DB->delete_records('course_completions', array('course' => $this->course_id));
        $DB->delete_records('course_completion_crit_compl', array('course' => $this->course_id));

                cache::make('core', 'completion')->purge();
    }

    
    public function delete_all_completion_data() {
        global $DB;

                $DB->delete_records_select('course_modules_completion',
                'coursemoduleid IN (SELECT id FROM {course_modules} WHERE course=?)',
                array($this->course_id));

                $this->delete_course_completion_data();
    }

    
    public function delete_all_state($cm) {
        global $DB;

                $DB->delete_records('course_modules_completion', array('coursemoduleid'=>$cm->id));

                $criteria = $this->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY);
        $acriteria = false;
        foreach ($criteria as $criterion) {
            if ($criterion->moduleinstance == $cm->id) {
                $acriteria = $criterion;
                break;
            }
        }

        if ($acriteria) {
                        $DB->delete_records('course_completion_crit_compl', array('course' => $this->course_id, 'criteriaid' => $acriteria->id));
            $DB->delete_records('course_completions', array('course' => $this->course_id));
        }

                cache::make('core', 'completion')->purge();
    }

    
    public function reset_all_state($cm) {
        global $DB;

        if ($cm->completion == COMPLETION_TRACKING_MANUAL) {
            $this->delete_all_state($cm);
            return;
        }
                $rs = $DB->get_recordset('course_modules_completion', array('coursemoduleid'=>$cm->id), '', 'userid');
        $keepusers = array();
        foreach ($rs as $rec) {
            $keepusers[] = $rec->userid;
        }
        $rs->close();

                $this->delete_all_state($cm);

                $trackedusers = $this->get_tracked_users();
        foreach ($trackedusers as $trackeduser) {
            $keepusers[] = $trackeduser->id;
        }
        $keepusers = array_unique($keepusers);

                foreach ($keepusers as $keepuser) {
            $this->update_state($cm, COMPLETION_UNKNOWN, $keepuser);
        }
    }

    
    public function get_data($cm, $wholecourse = false, $userid = 0, $modinfo = null) {
        global $USER, $CFG, $DB;
        $completioncache = cache::make('core', 'completion');

                if (!$userid) {
            $userid = $USER->id;
        }

                $usecache = $userid == $USER->id;
        $cacheddata = array();
        if ($usecache) {
            $key = $userid . '_' . $this->course->id;
            if (!isset($this->course->cacherev)) {
                $this->course = get_course($this->course_id);
            }
            if ($cacheddata = $completioncache->get($key)) {
                if ($cacheddata['cacherev'] != $this->course->cacherev) {
                                        $cacheddata = array();
                } else if (isset($cacheddata[$cm->id])) {
                    return (object)$cacheddata[$cm->id];
                }
            }
        }

                if ($usecache && $wholecourse) {
                        $alldatabycmc = $DB->get_records_sql("
    SELECT
        cmc.*
    FROM
        {course_modules} cm
        INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid=cm.id
    WHERE
        cm.course=? AND cmc.userid=?", array($this->course->id, $userid));

                        $alldata = array();
            foreach ($alldatabycmc as $data) {
                $alldata[$data->coursemoduleid] = (array)$data;
            }

                        if (empty($modinfo)) {
                $modinfo = get_fast_modinfo($this->course, $userid);
            }
            foreach ($modinfo->cms as $othercm) {
                if (isset($alldata[$othercm->id])) {
                    $data = $alldata[$othercm->id];
                } else {
                                        $data = array();
                    $data['id'] = 0;
                    $data['coursemoduleid'] = $othercm->id;
                    $data['userid'] = $userid;
                    $data['completionstate'] = 0;
                    $data['viewed'] = 0;
                    $data['timemodified'] = 0;
                }
                $cacheddata[$othercm->id] = $data;
            }

            if (!isset($cacheddata[$cm->id])) {
                $this->internal_systemerror("Unexpected error: course-module {$cm->id} could not be found on course {$this->course->id}");
            }

        } else {
                        $data = $DB->get_record('course_modules_completion', array('coursemoduleid'=>$cm->id, 'userid'=>$userid));
            if ($data) {
                $data = (array)$data;
            } else {
                                $data = array();
                $data['id'] = 0;
                $data['coursemoduleid'] = $cm->id;
                $data['userid'] = $userid;
                $data['completionstate'] = 0;
                $data['viewed'] = 0;
                $data['timemodified'] = 0;
            }

                        $cacheddata[$cm->id] = $data;
        }

        if ($usecache) {
            $cacheddata['cacherev'] = $this->course->cacherev;
            $completioncache->set($key, $cacheddata);
        }
        return (object)$cacheddata[$cm->id];
    }

    
    public function internal_set_data($cm, $data) {
        global $USER, $DB;

        $transaction = $DB->start_delegated_transaction();
        if (!$data->id) {
                        $data->id = $DB->get_field('course_modules_completion', 'id',
                    array('coursemoduleid'=>$data->coursemoduleid, 'userid'=>$data->userid));
        }
        if (!$data->id) {
                        $data->id = $DB->insert_record('course_modules_completion', $data);
        } else {
                        $DB->update_record('course_modules_completion', $data);
        }
        $transaction->allow_commit();

        $cmcontext = context_module::instance($data->coursemoduleid, MUST_EXIST);
        $coursecontext = $cmcontext->get_parent_context();

        $completioncache = cache::make('core', 'completion');
        if ($data->userid == $USER->id) {
                        if (!($cachedata = $completioncache->get($data->userid . '_' . $cm->course))
                    || $cachedata['cacherev'] != $this->course->cacherev) {
                $cachedata = array('cacherev' => $this->course->cacherev);
            }
            $cachedata[$cm->id] = $data;
            $completioncache->set($data->userid . '_' . $cm->course, $cachedata);

                        get_fast_modinfo($cm->course, 0, true);
        } else {
                        $completioncache->delete($data->userid . '_' . $cm->course);
        }

                $event = \core\event\course_module_completion_updated::create(array(
            'objectid' => $data->id,
            'context' => $cmcontext,
            'relateduserid' => $data->userid,
            'other' => array(
                'relateduserid' => $data->userid
            )
        ));
        $event->add_record_snapshot('course_modules_completion', $data);
        $event->trigger();
    }

     
    public function has_activities() {
        $modinfo = get_fast_modinfo($this->course);
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->completion != COMPLETION_TRACKING_NONE) {
                return true;
            }
        }
        return false;
    }

    
    public function get_activities() {
        $modinfo = get_fast_modinfo($this->course);
        $result = array();
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->completion != COMPLETION_TRACKING_NONE) {
                $result[$cm->id] = $cm;
            }
        }
        return $result;
    }

    
    public function is_tracked_user($userid) {
        return is_enrolled(context_course::instance($this->course->id), $userid, 'moodle/course:isincompletionreports', true);
    }

    
    public function get_num_tracked_users($where = '', $whereparams = array(), $groupid = 0) {
        global $DB;

        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
                context_course::instance($this->course->id), 'moodle/course:isincompletionreports', $groupid, true);
        $sql  = 'SELECT COUNT(eu.id) FROM (' . $enrolledsql . ') eu JOIN {user} u ON u.id = eu.id';
        if ($where) {
            $sql .= " WHERE $where";
        }

        $params = array_merge($enrolledparams, $whereparams);
        return $DB->count_records_sql($sql, $params);
    }

    
    public function get_tracked_users($where = '', $whereparams = array(), $groupid = 0,
             $sort = '', $limitfrom = '', $limitnum = '', context $extracontext = null) {

        global $DB;

        list($enrolledsql, $params) = get_enrolled_sql(
                context_course::instance($this->course->id),
                'moodle/course:isincompletionreports', $groupid, true);

        $allusernames = get_all_user_name_fields(true, 'u');
        $sql = 'SELECT u.id, u.idnumber, ' . $allusernames;
        if ($extracontext) {
            $sql .= get_extra_user_fields_sql($extracontext, 'u', '', array('idnumber'));
        }
        $sql .= ' FROM (' . $enrolledsql . ') eu JOIN {user} u ON u.id = eu.id';

        if ($where) {
            $sql .= " AND $where";
            $params = array_merge($params, $whereparams);
        }

        if ($sort) {
            $sql .= " ORDER BY $sort";
        }

        return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    
    public function get_progress_all($where = '', $where_params = array(), $groupid = 0,
            $sort = '', $pagesize = '', $start = '', context $extracontext = null) {
        global $CFG, $DB;

                $users = $this->get_tracked_users($where, $where_params, $groupid, $sort,
                $start, $pagesize, $extracontext);

                        $results = array();
        $userids = array();
        foreach ($users as $user) {
            $userids[] = $user->id;
            $results[$user->id] = $user;
            $results[$user->id]->progress = array();
        }

        for($i=0; $i<count($userids); $i+=1000) {
            $blocksize = count($userids)-$i < 1000 ? count($userids)-$i : 1000;

            list($insql, $params) = $DB->get_in_or_equal(array_slice($userids, $i, $blocksize));
            array_splice($params, 0, 0, array($this->course->id));
            $rs = $DB->get_recordset_sql("
                SELECT
                    cmc.*
                FROM
                    {course_modules} cm
                    INNER JOIN {course_modules_completion} cmc ON cm.id=cmc.coursemoduleid
                WHERE
                    cm.course=? AND cmc.userid $insql", $params);
            foreach ($rs as $progress) {
                $progress = (object)$progress;
                $results[$progress->userid]->progress[$progress->coursemoduleid] = $progress;
            }
            $rs->close();
        }

        return $results;
    }

    
    public function inform_grade_changed($cm, $item, $grade, $deleted) {
                                if (!$this->is_enabled($cm) ||
            $cm->completion == COMPLETION_TRACKING_MANUAL ||
            is_null($cm->completiongradeitemnumber) ||
            $item->itemnumber != $cm->completiongradeitemnumber) {
            return;
        }

                if ($deleted) {
                        $possibleresult = COMPLETION_INCOMPLETE;
        } else {
            $possibleresult = self::internal_get_grade_state($item, $grade);
        }

                $this->update_state($cm, $possibleresult, $grade->userid);
    }

    
    public static function internal_get_grade_state($item, $grade) {
                        if (!$grade || (is_null($grade->finalgrade) && is_null($grade->rawgrade))) {
            return COMPLETION_INCOMPLETE;
        }

                                if ($item->gradepass && $item->gradepass > 0.000009 && !$item->hidden) {
                        $score = !is_null($grade->finalgrade) ? $grade->finalgrade : $grade->rawgrade;

                        if ($score >= $item->gradepass) {
                return COMPLETION_COMPLETE_PASS;
            } else {
                return COMPLETION_COMPLETE_FAIL;
            }
        } else {
                        if (!is_null($grade->finalgrade) || !is_null($grade->rawgrade)) {
                                return COMPLETION_COMPLETE;
            } else {
                                return COMPLETION_INCOMPLETE;
            }
        }
    }

    
    public static function aggregate_completion_states($type, $old, $new) {
        if ($type == COMPLETION_AND) {
            return $old && $new;
        } else {
            return $old || $new;
        }
    }

    
    public function internal_systemerror($error) {
        global $CFG;
        throw new moodle_exception('err_system','completion',
            $CFG->wwwroot.'/course/view.php?id='.$this->course->id,null,$error);
    }
}
