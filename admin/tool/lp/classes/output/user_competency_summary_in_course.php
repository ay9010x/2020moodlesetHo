<?php


namespace tool_lp\output;

use renderable;
use renderer_base;
use templatable;
use core_competency\api;
use core_competency\user_competency;
use tool_lp\external\user_competency_summary_in_course_exporter;


class user_competency_summary_in_course implements renderable, templatable {

    
    protected $userid;

    
    protected $competencyid;

    
    protected $courseid;

    
    public function __construct($userid, $competencyid, $courseid) {
        $this->userid = $userid;
        $this->competencyid = $competencyid;
        $this->courseid = $courseid;
    }

    
    public function export_for_template(renderer_base $output) {
        global $DB;

        $usercompetencycourse = api::get_user_competency_in_course($this->courseid, $this->userid, $this->competencyid);
        $competency = $usercompetencycourse->get_competency();
        if (empty($usercompetencycourse) || empty($competency)) {
            throw new \invalid_parameter_exception('Invalid params. The competency does not belong to the course.');
        }

        $relatedcompetencies = api::list_related_competencies($competency->get_id());
        $user = $DB->get_record('user', array('id' => $this->userid));
        $evidence = api::list_evidence_in_course($this->userid, $this->courseid, $this->competencyid);
        $course = $DB->get_record('course', array('id' => $this->courseid));

        $params = array(
            'competency' => $competency,
            'usercompetencycourse' => $usercompetencycourse,
            'evidence' => $evidence,
            'user' => $user,
            'course' => $course,
            'scale' => $competency->get_scale(),
            'relatedcompetencies' => $relatedcompetencies
        );
        $exporter = new user_competency_summary_in_course_exporter(null, $params);
        $data = $exporter->export($output);

        return $data;
    }
}
