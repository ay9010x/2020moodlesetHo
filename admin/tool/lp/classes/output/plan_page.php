<?php


namespace tool_lp\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use stdClass;
use moodle_url;
use core_competency\api;
use core_competency\plan;
use core_competency\external\competency_exporter;
use core_competency\external\plan_exporter;
use tool_lp\external\competency_path_exporter;


class plan_page implements renderable, templatable {

    
    protected $plan;

    
    public function __construct($plan) {
        $this->plan = $plan;
    }

    
    public function export_for_template(\renderer_base $output) {
        $frameworks = array();
        $scales = array();

        $planexporter = new plan_exporter($this->plan, array('template' => $this->plan->get_template()));

        $data = new stdClass();
        $data->plan = $planexporter->export($output);
        $data->competencies = array();
        $data->pluginbaseurl = (new moodle_url('/admin/tool/lp'))->out(false);
        $data->contextid = $this->plan->get_context()->id;

        if ($data->plan->iscompleted) {
            $ucproperty = 'usercompetencyplan';
            $ucexporter = 'core_competency\\external\\user_competency_plan_exporter';
        } else {
            $ucproperty = 'usercompetency';
            $ucexporter = 'core_competency\\external\\user_competency_exporter';
        }

        $pclist = api::list_plan_competencies($this->plan);
        $proficientcount = 0;
        foreach ($pclist as $pc) {
            $comp = $pc->competency;
            $usercomp = $pc->$ucproperty;

                        if (!isset($frameworks[$comp->get_competencyframeworkid()])) {
                $frameworks[$comp->get_competencyframeworkid()] = $comp->get_framework();
            }
            $framework = $frameworks[$comp->get_competencyframeworkid()];

                        $scaleid = $comp->get_scaleid();
            $compscale = $comp->get_scale();
            if ($scaleid === null) {
                $scaleid = $framework->get_scaleid();
                $compscale = $framework->get_scale();
            }
            if (!isset($scales[$scaleid])) {
                $scales[$scaleid] = $compscale;
            }
            $scale = $scales[$scaleid];

                        $record = new stdClass();
            $exporter = new competency_exporter($comp, array('context' => $framework->get_context()));
            $record->competency = $exporter->export($output);

                        $exporter = new competency_path_exporter([
                'ancestors' => $comp->get_ancestors(),
                'framework' => $framework,
                'context' => $framework->get_context()
            ]);
            $record->comppath = $exporter->export($output);

            $exporter = new $ucexporter($usercomp, array('scale' => $scale));
            $record->$ucproperty = $exporter->export($output);

            $data->competencies[] = $record;
            if ($usercomp->get_proficiency()) {
                $proficientcount++;
            }
        }
        $data->competencycount = count($data->competencies);
        $data->proficientcompetencycount = $proficientcount;
        if ($data->competencycount) {
            $data->proficientcompetencypercentage = ((float) $proficientcount / (float) $data->competencycount) * 100.0;
        } else {
            $data->proficientcompetencypercentage = 0.0;
        }
        $data->proficientcompetencypercentageformatted = format_float($data->proficientcompetencypercentage);
        return $data;
    }
}
