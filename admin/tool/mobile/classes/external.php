<?php



namespace tool_mobile;

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_warnings;


class external extends external_api {

    
    public static function get_plugins_supporting_mobile_parameters() {
        return new external_function_parameters(array());
    }

    
    public static function get_plugins_supporting_mobile() {
        return array(
            'plugins' => api::get_plugins_supporting_mobile(),
            'warnings' => array(),
        );
    }

    
    public static function get_plugins_supporting_mobile_returns() {
        return new external_single_structure(
            array(
                'plugins' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'component' => new external_value(PARAM_COMPONENT, 'The plugin component name.'),
                            'version' => new external_value(PARAM_NOTAGS, 'The plugin version number.'),
                            'addon' => new external_value(PARAM_COMPONENT, 'The Mobile addon (package) name.'),
                            'dependencies' => new external_multiple_structure(
                                                new external_value(PARAM_COMPONENT, 'Mobile addon name.'),
                                                'The list of Mobile addons this addon depends on.'
                                               ),
                            'fileurl' => new external_value(PARAM_URL, 'The addon package url for download
                                                            or empty if it doesn\'t exist.'),
                            'filehash' => new external_value(PARAM_RAW, 'The addon package hash or empty if it doesn\'t exist.'),
                            'filesize' => new external_value(PARAM_INT, 'The addon package size or empty if it doesn\'t exist.')
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

}
