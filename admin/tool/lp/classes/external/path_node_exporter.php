<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();



class path_node_exporter extends \core_competency\external\exporter {

    
    protected static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED
            ],
            'name' => [
                'type' => PARAM_TEXT
            ],
            'first' => [
                'type' => PARAM_BOOL
            ],
            'last' => [
                'type' => PARAM_BOOL
            ],
            'position' => [
                'type' => PARAM_INT
            ]
        ];
    }
}
