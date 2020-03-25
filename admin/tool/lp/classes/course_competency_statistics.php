<?php



namespace tool_lp;
defined('MOODLE_INTERNAL') || die();

use core_competency\api;


class course_competency_statistics {

    
    public $competencycount = 0;

    
    public $proficientcompetencycount = 0;

    
    public $leastproficientcompetencies = array();

    
    public function __construct($courseid) {
        global $USER;

        $this->competencycount = api::count_competencies_in_course($courseid);
        $this->proficientcompetencycount = api::count_proficient_competencies_in_course_for_user($courseid, $USER->id);
        $this->leastproficientcompetencies = api::get_least_proficient_competencies_for_course($courseid, 0, 3);
    }
}
