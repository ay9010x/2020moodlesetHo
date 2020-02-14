<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use core_competency\api;
use core_competency\external\competency_exporter;


class related_competencies implements renderable, templatable {

    
    protected $relatedcompetencies = null;

    
    public function __construct($competencyid) {
        $this->competency = api::read_competency($competencyid);
        $this->context = $this->competency->get_context();
        $this->relatedcompetencies = api::list_related_competencies($competencyid);
    }

    
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->relatedcompetencies = array();
        if ($this->relatedcompetencies) {
            foreach ($this->relatedcompetencies as $competency) {
                $exporter = new competency_exporter($competency, array('context' => $this->context));
                $record = $exporter->export($output);
                $data->relatedcompetencies[] = $record;
            }
        }

                $data->showdeleterelatedaction = true;

        return $data;
    }
}
