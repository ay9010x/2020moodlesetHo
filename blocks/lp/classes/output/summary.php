<?php



namespace block_lp\output;
defined('MOODLE_INTERNAL') || die();

use core_competency\api;
use core_competency\external\competency_exporter;
use core_competency\external\plan_exporter;
use core_competency\external\user_competency_exporter;
use core_competency\external\user_summary_exporter;
use core_competency\plan;
use core_competency\url;
use renderable;
use renderer_base;
use templatable;


class summary implements renderable, templatable {

    
    protected $activeplans = array();
    
    protected $compstoreview = array();
    
    protected $planstoreview = array();
    
    protected $plans = array();
    
    protected $user;

    
    public function __construct($user = null) {
        global $USER;
        if (!$user) {
            $user = $USER;
        }
        $this->user = $user;

                $this->plans = api::list_user_plans($this->user->id);

                $this->compstoreview = api::list_user_competencies_to_review(0, 3);

                $this->planstoreview = api::list_plans_to_review(0, 3);
    }

    public function export_for_template(renderer_base $output) {
        $plans = array();
        foreach ($this->plans as $plan) {
            if (count($plans) >= 3) {
                break;
            }
            if ($plan->get_status() == plan::STATUS_ACTIVE) {
                $plans[] = $plan;
            }
        }
        $activeplans = array();
        foreach ($plans as $plan) {
            $planexporter = new plan_exporter($plan, array('template' => $plan->get_template()));
            $activeplans[] = $planexporter->export($output);
        }

        $compstoreview = array();
        foreach ($this->compstoreview['competencies'] as $compdata) {
            $ucexporter = new user_competency_exporter($compdata->usercompetency,
                array('scale' => $compdata->competency->get_scale()));
            $compexporter = new competency_exporter($compdata->competency,
                array('context' => $compdata->competency->get_context()));
            $userexporter = new user_summary_exporter($compdata->user);
            $compstoreview[] = array(
                'usercompetency' => $ucexporter->export($output),
                'competency' => $compexporter->export($output),
                'user' => $userexporter->export($output),
            );
        }

        $planstoreview = array();
        foreach ($this->planstoreview['plans'] as $plandata) {
            $planexporter = new plan_exporter($plandata->plan, array('template' => $plandata->template));
            $userexporter = new user_summary_exporter($plandata->owner);
            $planstoreview[] = array(
                'plan' => $planexporter->export($output),
                'user' => $userexporter->export($output),
            );
        }

        $data = array(
            'hasplans' => !empty($this->plans),
            'hasactiveplans' => !empty($activeplans),
            'hasmoreplans' => count($this->plans) > count($activeplans),
            'activeplans' => $activeplans,

            'compstoreview' => $compstoreview,
            'hascompstoreview' => $this->compstoreview['count'] > 0,
            'hasmorecompstoreview' => $this->compstoreview['count'] > 3,

            'planstoreview' => $planstoreview,
            'hasplanstoreview' => $this->planstoreview['count'] > 0,
            'hasmoreplanstoreview' => $this->planstoreview['count'] > 3,

            'plansurl' => url::plans($this->user->id)->out(false),
            'pluginbaseurl' => (new \moodle_url('/blocks/lp'))->out(false),
            'userid' => $this->user->id,
        );

        return $data;
    }

    
    public function has_content() {
        return !empty($this->plans) || $this->planstoreview['count'] > 0 || $this->compstoreview['count'] > 0;
    }

}
