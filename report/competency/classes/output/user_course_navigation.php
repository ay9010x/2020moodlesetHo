<?php


namespace report_competency\output;

use renderable;
use renderer_base;
use templatable;
use context_course;
use core_competency\external\user_summary_exporter;
use stdClass;


class user_course_navigation implements renderable, templatable {

    
    protected $userid;

    
    protected $courseid;

    
    protected $baseurl;

    
    public function __construct($userid, $courseid, $baseurl) {
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->baseurl = $baseurl;
    }

    
    public function export_for_template(renderer_base $output) {
        global $CFG, $DB, $SESSION, $PAGE;

        $context = context_course::instance($this->courseid);

        $data = new stdClass();
        $data->userid = $this->userid;
        $data->courseid = $this->courseid;
        $data->baseurl = $this->baseurl;
        $data->groupselector = '';

        if (has_any_capability(array('moodle/competency:usercompetencyview', 'moodle/competency:coursecompetencymanage'),
                $context)) {
            $course = $DB->get_record('course', array('id' => $this->courseid));
            $currentgroup = groups_get_course_group($course, true);
            if ($currentgroup !== false) {
                $select = groups_allgroups_course_menu($course, $PAGE->url, true, $currentgroup);
                $data->groupselector = $select;
            }
                        $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);

                        $groupmode = groups_get_course_groupmode($course);

            $users = get_enrolled_users($context, 'moodle/competency:coursecompetencygradable', $currentgroup,
                                        'u.*', null, 0, 0, $showonlyactiveenrol);

            $data->users = array();
            foreach ($users as $user) {
                $exporter = new user_summary_exporter($user);
                $user = $exporter->export($output);
                if ($user->id == $this->userid) {
                    $user->selected = true;
                }
                $data->users[] = $user;
            }
            $data->hasusers = true;
        } else {
            $data->users = array();
            $data->hasusers = false;
        }

        return $data;
    }
}
