<?php


namespace tool_lp\output;

use renderable;
use renderer_base;
use templatable;
use context_course;
use \core_competency\external\competency_exporter;
use \core_competency\external\user_summary_exporter;
use stdClass;


class user_competency_course_navigation implements renderable, templatable {

    
    protected $userid;

    
    protected $competencyid;

    
    protected $courseid;

    
    protected $baseurl;

    
    public function __construct($userid, $competencyid, $courseid, $baseurl) {
        $this->userid = $userid;
        $this->competencyid = $competencyid;
        $this->courseid = $courseid;
        $this->baseurl = $baseurl;
    }

    
    public function export_for_template(renderer_base $output) {
        global $CFG, $DB, $PAGE;

        $context = context_course::instance($this->courseid);

        $data = new stdClass();
        $data->userid = $this->userid;
        $data->competencyid = $this->competencyid;
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

        $coursecompetencies = \core_competency\api::list_course_competencies($this->courseid);
        $data->competencies = array();
        $contextcache = array();
        foreach ($coursecompetencies as $coursecompetency) {
            $frameworkid = $coursecompetency['competency']->get_competencyframeworkid();
            if (!isset($contextcache[$frameworkid])) {
                $contextcache[$frameworkid] = $coursecompetency['competency']->get_context();
            }
            $context = $contextcache[$frameworkid];
            $coursecompetencycontext = $context;
            $exporter = new competency_exporter($coursecompetency['competency'], array('context' => $coursecompetencycontext));
            $competency = $exporter->export($output);
            if ($competency->id == $this->competencyid) {
                $competency->selected = true;
            }
            $data->competencies[] = $competency;
        }
        $data->hascompetencies = count($data->competencies);
        return $data;
    }
}
