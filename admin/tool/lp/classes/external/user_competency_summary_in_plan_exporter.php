<?php


namespace tool_lp\external;

use context_user;
use renderer_base;
use stdClass;
use core_competency\external\plan_exporter;


class user_competency_summary_in_plan_exporter extends \core_competency\external\exporter {

    protected static function define_related() {
                return array('competency' => '\\core_competency\\competency',
                     'relatedcompetencies' => '\\core_competency\\competency[]',
                     'user' => '\\stdClass',
                     'plan' => '\\core_competency\\plan',
                     'usercompetency' => '\\core_competency\\user_competency?',
                     'usercompetencyplan' => '\\core_competency\\user_competency_plan?',
                     'evidence' => '\\core_competency\\evidence[]');
    }

    protected static function define_other_properties() {
        return array(
            'usercompetencysummary' => array(
                'type' => user_competency_summary_exporter::read_properties_definition()
            ),
            'plan' => array(
                'type' => plan_exporter::read_properties_definition(),
            )
        );
    }

    protected function get_other_values(renderer_base $output) {
                $related = $this->related;
                unset($related['plan']);
                $related['usercompetencycourse'] = null;
        $exporter = new user_competency_summary_exporter(null, $related);
        $result = new stdClass();
        $result->usercompetencysummary = $exporter->export($output);

        $exporter = new plan_exporter($this->related['plan'], array('template' => $this->related['plan']->get_template()));
        $result->plan = $exporter->export($output);

        return (array) $result;
    }
}
