<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;


class course_summary_exporter extends \core_competency\external\exporter {

    protected static function define_related() {
                return array('context' => '\\context');
    }

    protected function get_other_values(renderer_base $output) {
        return array(
            'viewurl' => (new moodle_url('/course/view.php', array('id' => $this->data->id)))->out(false)
        );
    }

    public static function define_properties() {
        return array(
            'id' => array(
                'type' => PARAM_INT,
            ),
            'fullname' => array(
                'type' => PARAM_TEXT,
            ),
            'shortname' => array(
                'type' => PARAM_TEXT,
            ),
            'idnumber' => array(
                'type' => PARAM_RAW,
            )
        );
    }

    public static function define_other_properties() {
        return array(
            'viewurl' => array(
                'type' => PARAM_URL,
            )
        );
    }
}
