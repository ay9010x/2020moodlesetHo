<?php


namespace tool_lp\output;

use renderable;
use renderer_base;
use templatable;
use context_course;
use \core_competency\external\competency_exporter;
use stdClass;


class competency_plan_navigation implements renderable, templatable {

    
    protected $userid;

    
    protected $competencyid;

    
    protected $planid;

    
    protected $baseurl;

    
    public function __construct($userid, $competencyid, $planid, $baseurl) {
        $this->userid = $userid;
        $this->competencyid = $competencyid;
        $this->planid = $planid;
        $this->baseurl = $baseurl;
    }

    
    public function export_for_template(renderer_base $output) {

        $data = new stdClass();
        $data->userid = $this->userid;
        $data->competencyid = $this->competencyid;
        $data->planid = $this->planid;
        $data->baseurl = $this->baseurl;

        $plancompetencies = \core_competency\api::list_plan_competencies($data->planid);
        $data->competencies = array();
        $contextcache = array();
        foreach ($plancompetencies as $plancompetency) {
            $frameworkid = $plancompetency->competency->get_competencyframeworkid();
            if (!isset($contextcache[$frameworkid])) {
                $contextcache[$frameworkid] = $plancompetency->competency->get_context();
            }
            $context = $contextcache[$frameworkid];
            $exporter = new competency_exporter($plancompetency->competency, array('context' => $context));
            $competency = $exporter->export($output);
            if ($competency->id == $this->competencyid) {
                $competency->selected = true;
            }
            $data->competencies[] = $competency;
        }
        $data->hascompetencies = count($data->competencies);
        return $data;
    }
}
