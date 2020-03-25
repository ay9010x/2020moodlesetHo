<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use core_competency\api;
use tool_lp\external\competency_summary_exporter;


class competency_summary implements renderable, templatable {

    
    protected $framework = null;

    
    protected $competency = null;

    
    protected $relatedcompetencies = array();

    
    protected $courses = array();

    
    public function __construct($competency, $framework, $includerelated, $includecourses) {
        $this->competency = $competency;
        $this->framework = $framework;
        if ($includerelated) {
            $this->relatedcompetencies = api::list_related_competencies($competency->get_id());
        }

        if ($includecourses) {
            $this->courses = api::list_courses_using_competency($competency->get_id());
        }
    }

    
    public function export_for_template(renderer_base $output) {
        $related = array(
            'context' => $this->framework->get_context(),
            'framework' => $this->framework,
            'linkedcourses' => $this->courses,
            'relatedcompetencies' => $this->relatedcompetencies,
            'competency' => $this->competency
        );

        $exporter = new competency_summary_exporter($this->competency, $related);
        $data = $exporter->export($output);

        return $data;
    }
}
