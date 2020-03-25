<?php



defined('MOODLE_INTERNAL') || die();


function atto_accessibilityhelper_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('liststyles',
                                    'nostyles',
                                    'listlinks',
                                    'nolinks',
                                    'selectlink',
                                    'listimages',
                                    'noimages',
                                    'selectimage'),
                                    'atto_accessibilityhelper');
}

