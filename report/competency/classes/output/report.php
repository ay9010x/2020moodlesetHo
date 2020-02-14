<?php


namespace report_competency\output;

use context_course;
use renderable;
use core_user;
use templatable;
use renderer_base;
use moodle_url;
use stdClass;
use core_competency\api;
use core_competency\external\user_competency_course_exporter;
use core_competency\external\user_summary_exporter;
use core_competency\url;
use core_competency\user_competency;
use tool_lp\external\competency_summary_exporter;
use tool_lp\external\course_summary_exporter;


class report implements renderable, templatable {

    
    protected $context;
    
    protected $courseid;
    
    protected $competencies;

    
    public function __construct($courseid, $userid) {
        $this->courseid = $courseid;
        $this->userid = $userid;
        $this->context = context_course::instance($courseid);
    }

    
    public function export_for_template(renderer_base $output) {
        global $DB;

        $data = new stdClass();
        $data->courseid = $this->courseid;

        $course = $DB->get_record('course', array('id' => $this->courseid));
        $coursecontext = context_course::instance($course->id);
        $exporter = new course_summary_exporter($course, array('context' => $coursecontext));
        $coursecompetencysettings = api::read_course_competency_settings($course->id);
        $data->pushratingstouserplans = $coursecompetencysettings->get_pushratingstouserplans();
        $data->course = $exporter->export($output);

        $data->usercompetencies = array();
        $scalecache = array();
        $frameworkcache = array();

        $user = core_user::get_user($this->userid);

        $exporter = new user_summary_exporter($user);
        $data->user = $exporter->export($output);
        $data->usercompetencies = array();
        $coursecompetencies = api::list_course_competencies($this->courseid);
        $usercompetencycourses = api::list_user_competencies_in_course($this->courseid, $user->id);

        foreach ($usercompetencycourses as $usercompetencycourse) {
            $onerow = new stdClass();
            $competency = null;
            foreach ($coursecompetencies as $coursecompetency) {
                if ($coursecompetency['competency']->get_id() == $usercompetencycourse->get_competencyid()) {
                    $competency = $coursecompetency['competency'];
                    break;
                }
            }
            if (!$competency) {
                continue;
            }
                        if (!isset($frameworkcache[$competency->get_competencyframeworkid()])) {
                $frameworkcache[$competency->get_competencyframeworkid()] = $competency->get_framework();
            }
            $framework = $frameworkcache[$competency->get_competencyframeworkid()];

                        $scaleid = $competency->get_scaleid();
            if ($scaleid === null) {
                $scaleid = $framework->get_scaleid();
                if (!isset($scalecache[$scaleid])) {
                    $scalecache[$competency->get_scaleid()] = $framework->get_scale();
                }

            } else if (!isset($scalecache[$scaleid])) {
                $scalecache[$competency->get_scaleid()] = $competency->get_scale();
            }
            $scale = $scalecache[$competency->get_scaleid()];

            $exporter = new user_competency_course_exporter($usercompetencycourse, array('scale' => $scale));
            $record = $exporter->export($output);
            $onerow->usercompetencycourse = $record;
            $exporter = new competency_summary_exporter(null, array(
                'competency' => $competency,
                'framework' => $framework,
                'context' => $framework->get_context(),
                'relatedcompetencies' => array(),
                'linkedcourses' => array()
            ));
            $onerow->competency = $exporter->export($output);
            array_push($data->usercompetencies, $onerow);
        }

        return $data;
    }
}
