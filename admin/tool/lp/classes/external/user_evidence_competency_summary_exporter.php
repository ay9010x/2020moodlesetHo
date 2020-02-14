<?php


namespace tool_lp\external;

use moodle_url;
use renderer_base;
use core_competency\external\competency_exporter;
use core_competency\external\user_competency_exporter;


class user_evidence_competency_summary_exporter extends \core_competency\external\exporter {

    protected static function define_related() {
        return array('competency' => '\\core_competency\\competency',
                     'usercompetency' => '\\core_competency\\user_competency',
                     'scale' => 'grade_scale',
                     'context' => '\\context'
                    );
    }

    protected static function define_other_properties() {
        return array(
            'competency' => array(
                'type' => competency_exporter::read_properties_definition()
            ),
            'usercompetency' => array(
                'type' => user_competency_exporter::read_properties_definition(),
            )
        );
    }

    protected function get_other_values(renderer_base $output) {
        $competencyexporter = new competency_exporter($this->related['competency'],
                array('context' => $this->related['context']));
        $usercompetencyexporter = new user_competency_exporter($this->related['usercompetency'],
                array('scale' => $this->related['scale']));

        $values = array(
            'competency' => $competencyexporter->export($output),
            'usercompetency' => $usercompetencyexporter->export($output)
        );

        return $values;
    }

}
