<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use core_competency\api;
use tool_lp\external\user_competency_summary_in_plan_exporter;


class user_competency_summary_in_plan implements renderable, templatable {

    
    protected $competencyid;

    
    protected $planid;

    
    public function __construct($competencyid, $planid) {
        $this->competencyid = $competencyid;
        $this->planid = $planid;
    }

    
    public function export_for_template(\renderer_base $output) {
        global $DB;

        $plan = api::read_plan($this->planid);
        $pc = api::get_plan_competency($plan, $this->competencyid);
        $competency = $pc->competency;
        $usercompetency = $pc->usercompetency;
        $usercompetencyplan = $pc->usercompetencyplan;

        if (empty($competency)) {
            throw new \invalid_parameter_exception('Invalid params. The competency does not belong to the plan.');
        }

        $relatedcompetencies = api::list_related_competencies($competency->get_id());
        $userid = $plan->get_userid();
        $user = $DB->get_record('user', array('id' => $userid));
        $evidence = api::list_evidence($userid, $this->competencyid, $plan->get_id());

        $params = array(
            'competency' => $competency,
            'usercompetency' => $usercompetency,
            'usercompetencyplan' => $usercompetencyplan,
            'evidence' => $evidence,
            'user' => $user,
            'plan' => $plan,
            'relatedcompetencies' => $relatedcompetencies
        );
        $exporter = new user_competency_summary_in_plan_exporter(null, $params);
        $data = $exporter->export($output);

        return $data;
    }
}
