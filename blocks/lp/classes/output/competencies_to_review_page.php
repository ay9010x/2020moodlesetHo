<?php


namespace block_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use core_competency\api;
use core_competency\external\competency_exporter;
use core_competency\external\user_competency_exporter;
use core_competency\external\user_summary_exporter;


class competencies_to_review_page implements renderable, templatable {

    
    protected $compstoreview;

    
    public function __construct() {
        $this->compstoreview = api::list_user_competencies_to_review(0, 1000);
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

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

        $data = array(
            'competencies' => $compstoreview,
            'pluginbaseurl' => (new moodle_url('/blocks/lp'))->out(false),
        );

        return $data;
    }

}
