<?php


namespace tool_templatelibrary;

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;


class external extends external_api {

    
    public static function list_templates_parameters() {
        $component = new external_value(
            PARAM_COMPONENT,
            'The component to search',
            VALUE_DEFAULT,
            ''
        );
        $search = new external_value(
            PARAM_RAW,
            'The search string',
            VALUE_DEFAULT,
            ''
        );
        $themename = new external_value(
            PARAM_COMPONENT,
            'The current theme',
            VALUE_DEFAULT,
            ''
        );
        $params = array('component' => $component, 'search' => $search, 'themename' => $themename);
        return new external_function_parameters($params);
    }

    
    public static function list_templates($component, $search, $themename = '') {
        $params = self::validate_parameters(self::list_templates_parameters(),
                                            array(
                                                'component' => $component,
                                                'search' => $search,
                                                'themename' => $themename,
                                            ));

        return api::list_templates($component, $search, $themename);
    }

    
    public static function list_templates_returns() {
        return new external_multiple_structure(new external_value(PARAM_RAW, 'The template name (format is component/templatename)'));
    }

    
    public static function load_canonical_template_parameters() {
        return new external_function_parameters(
                array('component' => new external_value(PARAM_COMPONENT, 'component containing the template'),
                      'template' => new external_value(PARAM_ALPHANUMEXT, 'name of the template'))
            );
    }

    
    public static function load_canonical_template($component, $template) {
        $params = self::validate_parameters(self::load_canonical_template_parameters(),
                                            array('component' => $component,
                                                  'template' => $template));

        $component = $params['component'];
        $template = $params['template'];

        return api::load_canonical_template($component, $template);
    }

    
    public static function load_canonical_template_returns() {
        return new external_value(PARAM_RAW, 'template');
    }
}
