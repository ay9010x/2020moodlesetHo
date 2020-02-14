<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;



class course_module_summary_exporter extends \core_competency\external\exporter {

    protected static function define_related() {
        return array('cm' => 'cm_info');
    }

    protected function get_other_values(renderer_base $output) {
        $cm = $this->related['cm'];
        $context = $cm->context;

        $values = array(
            'id' => $cm->id,
            'name' => external_format_string($cm->name, $context->id),
            'iconurl' => $cm->get_icon_url()->out()
        );
        if ($cm->url) {
            $values['url'] = $cm->url->out();
        }
        return $values;
    }


    public static function define_other_properties() {
        return array(
            'id' => array(
                'type' => PARAM_INT,
            ),
            'name' => array(
                'type' => PARAM_TEXT
            ),
            'url' => array(
                'type' => PARAM_URL,
                'optional' => true,
            ),
            'iconurl' => array(
                'type' => PARAM_URL
            )
        );
    }
}
