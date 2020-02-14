<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;
use core_competency\external\competency_exporter;


class template_statistics_exporter extends \core_competency\external\exporter {

    public static function define_properties() {
        return array(
            'competencycount' => array(
                'type' => PARAM_INT,
            ),
            'unlinkedcompetencycount' => array(
                'type' => PARAM_INT,
            ),
            'plancount' => array(
                'type' => PARAM_INT,
            ),
            'completedplancount' => array(
                'type' => PARAM_INT,
            ),
            'usercompetencyplancount' => array(
                'type' => PARAM_INT,
            ),
            'proficientusercompetencyplancount' => array(
                'type' => PARAM_INT,
            )
        );
    }

    public static function define_other_properties() {
        return array(
            'linkedcompetencypercentage' => array(
                'type' => PARAM_FLOAT
            ),
            'linkedcompetencypercentageformatted' => array(
                'type' => PARAM_RAW
            ),
            'linkedcompetencycount' => array(
                'type' => PARAM_INT
            ),
            'completedplanpercentage' => array(
                'type' => PARAM_FLOAT
            ),
            'completedplanpercentageformatted' => array(
                'type' => PARAM_RAW
            ),
            'proficientusercompetencyplanpercentage' => array(
                'type' => PARAM_FLOAT
            ),
            'proficientusercompetencyplanpercentageformatted' => array(
                'type' => PARAM_RAW
            ),
            'leastproficient' => array(
                'type' => competency_exporter::read_properties_definition(),
                'multiple' => true
            ),
            'leastproficientcount' => array(
                'type' => PARAM_INT
            ),
        );
    }

    protected function get_other_values(renderer_base $output) {
        $linkedcompetencycount = $this->data->competencycount - $this->data->unlinkedcompetencycount;
        if ($linkedcompetencycount < 0) {
                        $linkedcompetencycount = 0;
        }
        $linkedcompetencypercentage = 0;
        $linkedcompetencypercentageformatted = '';
        if ($this->data->competencycount > 0) {
            $linkedcompetencypercentage = ((float) $linkedcompetencycount / (float) $this->data->competencycount) * 100.0;
            $linkedcompetencypercentageformatted = format_float($linkedcompetencypercentage);
        }
        $completedplanpercentage = 0;
        $completedplanpercentageformatted = '';
        if ($this->data->plancount > 0) {
            $completedplanpercentage = ((float) $this->data->completedplancount / (float) $this->data->plancount) * 100.0;
            $completedplanpercentageformatted = format_float($completedplanpercentage);
        }
        $proficientusercompetencyplanpercentage = 0;
        $proficientusercompetencyplanpercentageformatted = '';
        if ($this->data->usercompetencyplancount > 0) {
            $proficientusercompetencyplanpercentage = ((float) $this->data->proficientusercompetencyplancount
                    / (float) $this->data->usercompetencyplancount) * 100.0;
            $proficientusercompetencyplanpercentageformatted = format_float($proficientusercompetencyplanpercentage);
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
            'linkedcompetencycount' => $linkedcompetencycount,
            'linkedcompetencypercentage' => $linkedcompetencypercentage,
            'linkedcompetencypercentageformatted' => $linkedcompetencypercentageformatted,
            'completedplanpercentage' => $completedplanpercentage,
            'completedplanpercentageformatted' => $completedplanpercentageformatted,
            'proficientusercompetencyplanpercentage' => $proficientusercompetencyplanpercentage,
            'proficientusercompetencyplanpercentageformatted' => $proficientusercompetencyplanpercentageformatted,
            'leastproficient' => $competencies,
            'leastproficientcount' => count($competencies)
        );
    }
}
