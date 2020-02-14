<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;
use core_competency\external\competency_exporter;


class course_competency_statistics_exporter extends \core_competency\external\exporter {

    public static function define_properties() {
        return array(
            'competencycount' => array(
                'type' => PARAM_INT,
            ),
            'proficientcompetencycount' => array(
                'type' => PARAM_INT,
            ),
        );
    }

    public static function define_other_properties() {
        return array(
            'proficientcompetencypercentage' => array(
                'type' => PARAM_FLOAT
            ),
            'proficientcompetencypercentageformatted' => array(
                'type' => PARAM_RAW
            ),
            'leastproficient' => array(
                'type' => competency_exporter::read_properties_definition(),
                'multiple' => true
            ),
            'leastproficientcount' => array(
                'type' => PARAM_INT
            ),
            'canbegradedincourse' => array(
                'type' => PARAM_BOOL
            ),
            'canmanagecoursecompetencies' => array(
                'type' => PARAM_BOOL
            ),
        );
    }

    protected static function define_related() {
        return array('context' => 'context');
    }

    protected function get_other_values(renderer_base $output) {
        $proficientcompetencypercentage = 0;
        $proficientcompetencypercentageformatted = '';
        if ($this->data->competencycount > 0) {
            $proficientcompetencypercentage = ((float) $this->data->proficientcompetencycount
                / (float) $this->data->competencycount) * 100.0;
            $proficientcompetencypercentageformatted = format_float($proficientcompetencypercentage);
        }
        $competencies = array();
        $contextcache = array();
        foreach ($this->data->leastproficientcompetencies as $competency) {
            if (!isset($contextcache[$competency->get_competencyframeworkid()])) {
                $contextcache[$competency->get_competencyframeworkid()] = $competency->get_context();
            }
            $context = $contextcache[$competency->get_competencyframeworkid()];
            $exporter = new competency_exporter($competency, array('context' => $context));
            $competencies[] = $exporter->export($output);
        }
        return array(
            'proficientcompetencypercentage' => $proficientcompetencypercentage,
            'proficientcompetencypercentageformatted' => $proficientcompetencypercentageformatted,
            'leastproficient' => $competencies,
            'leastproficientcount' => count($competencies),
            'canbegradedincourse' => has_capability('moodle/competency:coursecompetencygradable', $this->related['context']),
            'canmanagecoursecompetencies' => has_capability('moodle/competency:coursecompetencymanage', $this->related['context'])
        );
    }
}
