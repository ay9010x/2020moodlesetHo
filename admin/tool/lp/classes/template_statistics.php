<?php



namespace tool_lp;
defined('MOODLE_INTERNAL') || die();

use core_competency\api;
use core_competency\plan;
use core_competency\template;


class template_statistics {

    
    public $competencycount = 0;

    
    public $unlinkedcompetencycount = 0;

    
    public $plancount = 0;

    
    public $completedplancount = 0;

    
    public $usercompetencyplancount = 0;

    
    public $proficientusercompetencyplancount = 0;

    
    public $leastproficientcompetencies = array();

    
    public function __construct($templateid) {
        $template = new template($templateid);
        $this->competencycount = api::count_competencies_in_template($template);
        $this->unlinkedcompetencycount = api::count_competencies_in_template_with_no_courses($template);

        $this->plancount = api::count_plans_for_template($template, 0);
        $this->completedplancount = api::count_plans_for_template($template, plan::STATUS_COMPLETE);

        $this->usercompetencyplancount = api::count_user_competency_plans_for_template($template);
        $this->proficientusercompetencyplancount = api::count_user_competency_plans_for_template($template, true);

        $this->leastproficientcompetencies = api::get_least_proficient_competencies_for_template($template, 0, 3);
    }
}
