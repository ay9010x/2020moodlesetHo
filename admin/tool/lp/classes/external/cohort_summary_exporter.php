<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;


class cohort_summary_exporter extends \core_competency\external\exporter {

    protected static function define_related() {
                return array('context' => '\\context');
    }

    public static function define_properties() {
        return array(
            'id' => array(
                'type' => PARAM_INT,
            ),
            'name' => array(
                'type' => PARAM_TEXT,
            ),
            'idnumber' => array(
                'type' => PARAM_RAW,                        'default' => '',
                'null' => NULL_ALLOWED
            ),
            'visible' => array(
                'type' => PARAM_BOOL,
            )
        );
    }

    public static function define_other_properties() {
        return array(
            'contextname' => array(
                'type' => PARAM_TEXT
            ),
        );
    }

    protected function get_other_values(renderer_base $output) {
        return array(
            'contextname' => $this->related['context']->get_context_name()
        );
    }
}
