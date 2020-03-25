<?php


namespace block_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use core_competency\api;
use core_competency\external\plan_exporter;
use core_competency\external\user_summary_exporter;


class plans_to_review_page implements renderable, templatable {

    
    protected $planstoreview;

    
    public function __construct() {
        $this->planstoreview = api::list_plans_to_review(0, 1000);
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

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
            'plans' => $planstoreview,
            'pluginbaseurl' => (new moodle_url('/blocks/lp'))->out(false),
        );

        return $data;
    }

}
