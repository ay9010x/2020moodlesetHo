<?php



namespace core\output;

use external_api;
use external_function_parameters;
use external_value;
use core_component;
use moodle_exception;
use context_system;
use theme_config;


class external extends external_api {
    
    public static function load_template_parameters() {
        return new external_function_parameters(
                array('component' => new external_value(PARAM_COMPONENT, 'component containing the template'),
                      'template' => new external_value(PARAM_ALPHANUMEXT, 'name of the template'),
                      'themename' => new external_value(PARAM_ALPHANUMEXT, 'The current theme.'),
                         )
            );
    }

    
    public static function load_template($component, $template, $themename) {
        global $DB, $CFG, $PAGE;

        $params = self::validate_parameters(self::load_template_parameters(),
                                            array('component' => $component,
                                                  'template' => $template,
                                                  'themename' => $themename));

        $component = $params['component'];
        $template = $params['template'];
        $themename = $params['themename'];

        $templatename = $component . '/' . $template;

                $filename = mustache_template_finder::get_template_filepath($templatename, $themename);
        $templatestr = file_get_contents($filename);

        return $templatestr;
    }

    
    public static function load_template_returns() {
        return new external_value(PARAM_RAW, 'template');
    }
}

