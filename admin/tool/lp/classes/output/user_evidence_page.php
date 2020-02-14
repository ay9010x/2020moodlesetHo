<?php


namespace tool_lp\output;

use moodle_url;
use renderable;
use templatable;
use stdClass;
use core_competency\api;
use tool_lp\external\user_evidence_summary_exporter;


class user_evidence_page implements renderable, templatable {

    
    protected $context;

    
    protected $userevidence;

    
    public function __construct($userevidence) {
        $this->userevidence = $userevidence;
        $this->context = $this->userevidence->get_context();
    }

    
    public function export_for_template(\renderer_base $output) {
        $data = new stdClass();

        $userevidencesummaryexporter = new user_evidence_summary_exporter($this->userevidence, array(
            'context' => $this->context));
        $data->userevidence = $userevidencesummaryexporter->export($output);
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(true);

        return $data;
    }
}
