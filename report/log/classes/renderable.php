<?php



defined('MOODLE_INTERNAL') || die;
use core\log\manager;


class report_log_renderable implements renderable {
    
    protected $logmanager;

    
    public $selectedlogreader = null;

    
    public $page;

    
    public $perpage;

    
    public $course;

    
    public $url;

    
    public $date;

    
    public $userid;

    
    public $modid;

    
    public $action;

    
    public $edulevel;

    
    public $showcourses;

    
    public $showusers;

    
    public $showreport;

    
    public $showselectorform;

    
    public $logformat;

    
    public $order;

    
    public $groupid;

    
    public $tablelog;

    
    public function __construct($logreader = "", $course = 0, $userid = 0, $modid = 0, $action = "", $groupid = 0, $edulevel = -1,
            $showcourses = false, $showusers = false, $showreport = true, $showselectorform = true, $url = "", $date = 0,
            $logformat='showashtml', $page = 0, $perpage = 100, $order = "timecreated ASC") {

        global $PAGE;

                if (empty($logreader)) {
            $readers = $this->get_readers();
            if (!empty($readers)) {
                reset($readers);
                $logreader = key($readers);
            } else {
                $logreader = null;
            }
        }
                if (empty($url)) {
            $url = new moodle_url($PAGE->url);
        } else {
            $url = new moodle_url($url);
        }
        $this->selectedlogreader = $logreader;
        $url->param('logreader', $logreader);

                if (!empty($course) && is_int($course)) {
            $course = get_course($course);
        }
        $this->course = $course;

        $this->userid = $userid;
        $this->date = $date;
        $this->page = $page;
        $this->perpage = $perpage;
        $this->url = $url;
        $this->order = $order;
        $this->modid = $modid;
        $this->action = $action;
        $this->groupid = $groupid;
        $this->edulevel = $edulevel;
        $this->showcourses = $showcourses;
        $this->showusers = $showusers;
        $this->showreport = $showreport;
        $this->showselectorform = $showselectorform;
        $this->logformat = $logformat;
    }

    
    public function get_readers($nameonly = false) {
        if (!isset($this->logmanager)) {
            $this->logmanager = get_log_manager();
        }

        $readers = $this->logmanager->get_readers('core\log\sql_reader');
        if ($nameonly) {
            foreach ($readers as $pluginname => $reader) {
                $readers[$pluginname] = $reader->get_name();
            }
        }
        return $readers;
    }

    
    public function get_activities_list() {
        $activities = array();

                $sitecontext = context_system::instance();
        if ($this->course->id == SITEID && has_capability('report/log:view', $sitecontext)) {
            $activities["site_errors"] = get_string("siteerrors");
            return $activities;
        }

        $modinfo = get_fast_modinfo($this->course);
        if (!empty($modinfo->cms)) {
            $section = 0;
            $thissection = array();
            foreach ($modinfo->cms as $cm) {
                if (!$cm->uservisible || !$cm->has_view()) {
                    continue;
                }
                if ($cm->sectionnum > 0 and $section <> $cm->sectionnum) {
                    $activities[] = $thissection;
                    $thissection = array();
                }
                $section = $cm->sectionnum;
                $modname = strip_tags($cm->get_formatted_name());
                if (core_text::strlen($modname) > 55) {
                    $modname = core_text::substr($modname, 0, 50)."...";
                }
                if (!$cm->visible) {
                    $modname = "(".$modname.")";
                }
                $key = get_section_name($this->course, $cm->sectionnum);
                if (!isset($thissection[$key])) {
                    $thissection[$key] = array();
                }
                $thissection[$key][$cm->id] = $modname;
            }
            if (!empty($thissection)) {
                $activities[] = $thissection;
            }
        }
        return $activities;
    }

    
    public function get_selected_group() {
        global $SESSION, $USER;

                if (empty($this->course)) {
            return 0;
        }

        $context = context_course::instance($this->course->id);

        $selectedgroup = 0;
                $groupmode = groups_get_course_groupmode($this->course);
        if ($groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
            $selectedgroup = -1;
        } else if ($groupmode) {
            $selectedgroup = $this->groupid;
        } else {
            $selectedgroup = 0;
        }

        if ($selectedgroup === -1) {
            if (isset($SESSION->currentgroup[$this->course->id])) {
                $selectedgroup = $SESSION->currentgroup[$this->course->id];
            } else {
                $selectedgroup = groups_get_all_groups($this->course->id, $USER->id);
                if (is_array($selectedgroup)) {
                    $groupids = array_keys($selectedgroup);
                    $selectedgroup = array_shift($groupids);
                    $SESSION->currentgroup[$this->course->id] = $selectedgroup;
                } else {
                    $selectedgroup = 0;
                }
            }
        }
        return $selectedgroup;
    }

    
    public function get_actions() {
        $actions = array(
                'c' => get_string('create'),
                'r' => get_string('view'),
                'u' => get_string('update'),
                'd' => get_string('delete'),
                'cud' => get_string('allchanges')
                );
        return $actions;
    }

    
    public function get_selected_user_fullname() {
        $user = core_user::get_user($this->userid);
        return fullname($user);
    }

    
    public function get_course_list() {
        global $DB, $SITE;

        $courses = array();

        $sitecontext = context_system::instance();
                $numcourses = $DB->count_records("course");
        if ($numcourses < COURSE_MAX_COURSES_PER_DROPDOWN && !$this->showcourses) {
            $this->showcourses = 1;
        }

                if (has_capability('report/log:view', $sitecontext) && $this->showcourses) {
            if ($courserecords = $DB->get_records("course", null, "fullname", "id,shortname,fullname,category")) {
                foreach ($courserecords as $course) {
                    if ($course->id == SITEID) {
                        $courses[$course->id] = format_string($course->fullname) . ' (' . get_string('site') . ')';
                    } else {
                        $courses[$course->id] = format_string(get_course_display_name_for_list($course));
                    }
                }
            }
            core_collator::asort($courses);
        }
        return $courses;
    }

    
    public function get_group_list() {

                if (empty($this->course)) {
            return array();
        }

        $context = context_course::instance($this->course->id);
        $groups = array();
        $groupmode = groups_get_course_groupmode($this->course);
        if (($groupmode == VISIBLEGROUPS) ||
                ($groupmode == SEPARATEGROUPS and has_capability('moodle/site:accessallgroups', $context))) {
                        if ($cgroups = groups_get_all_groups($this->course->id)) {
                foreach ($cgroups as $cgroup) {
                    $groups[$cgroup->id] = $cgroup->name;
                }
            }
        }
        return $groups;
    }

    
    public function get_user_list() {
        global $CFG, $SITE;

        $courseid = $SITE->id;
        if (!empty($this->course)) {
            $courseid = $this->course->id;
        }
        $context = context_course::instance($courseid);
        $limitfrom = empty($this->showusers) ? 0 : '';
        $limitnum  = empty($this->showusers) ? COURSE_MAX_USERS_PER_DROPDOWN + 1 : '';
        $courseusers = get_enrolled_users($context, '', $this->groupid, 'u.id, ' . get_all_user_name_fields(true, 'u'),
                null, $limitfrom, $limitnum);

        if (count($courseusers) < COURSE_MAX_USERS_PER_DROPDOWN && !$this->showusers) {
            $this->showusers = 1;
        }

        $users = array();
        if ($this->showusers) {
            if ($courseusers) {
                foreach ($courseusers as $courseuser) {
                     $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
                }
            }
            $users[$CFG->siteguest] = get_string('guestuser');
        }
        return $users;
    }

    
    public function get_date_options() {
        global $SITE;

        $strftimedate = get_string("strftimedate");
        $strftimedaydate = get_string("strftimedaydate");

                                $timenow = time(); 
                $timemidnight = usergetmidnight($timenow);

                $dates = array("$timemidnight" => get_string("today").", ".userdate($timenow, $strftimedate) );

                $course = $SITE;
        if (!empty($this->course)) {
            $course = $this->course;
        }
        if (!$course->startdate or ($course->startdate > $timenow)) {
            $course->startdate = $course->timecreated;
        }

        $numdates = 1;
        while ($timemidnight > $course->startdate and $numdates < 365) {
            $timemidnight = $timemidnight - 86400;
            $timenow = $timenow - 86400;
            $dates["$timemidnight"] = userdate($timenow, $strftimedaydate);
            $numdates++;
        }
        return $dates;
    }

    
    public function get_edulevel_options() {
        $edulevels = array(
                    -1 => get_string("edulevel"),
                    1 => get_string('edulevelteacher'),
                    2 => get_string('edulevelparticipating'),
                    0 => get_string('edulevelother')
                    );
        return $edulevels;
    }

    
    public function setup_table() {
        $readers = $this->get_readers();

        $filter = new \stdClass();
        if (!empty($this->course)) {
            $filter->courseid = $this->course->id;
        } else {
            $filter->courseid = 0;
        }

        $filter->userid = $this->userid;
        $filter->modid = $this->modid;
        $filter->groupid = $this->get_selected_group();
        $filter->logreader = $readers[$this->selectedlogreader];
        $filter->edulevel = $this->edulevel;
        $filter->action = $this->action;
        $filter->date = $this->date;
        $filter->orderby = $this->order;

                if ('site_errors' === $this->modid) {
            $filter->siteerrors = true;
            $filter->modid = 0;
        }

        $this->tablelog = new report_log_table_log('report_log', $filter);
        $this->tablelog->define_baseurl($this->url);
        $this->tablelog->is_downloadable(true);
        $this->tablelog->show_download_buttons_at(array(TABLE_P_BOTTOM));
    }

    
    public function download() {
        $filename = 'logs_' . userdate(time(), get_string('backupnameformat', 'langconfig'), 99, false);
        if ($this->course->id !== SITEID) {
            $courseshortname = format_string($this->course->shortname, true,
                    array('context' => context_course::instance($this->course->id)));
            $filename = clean_filename('logs_' . $courseshortname . '_' . userdate(time(),
                    get_string('backupnameformat', 'langconfig'), 99, false));
        }
        $this->tablelog->is_downloading($this->logformat, $filename);
        $this->tablelog->out($this->perpage, false);
    }
}
