<?php



defined('MOODLE_INTERNAL') || die();


function atto_link_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('createlink',
                                          'unlink',
                                          'enterurl',
                                          'browserepositories',
                                          'openinnewwindow'),
                                    'atto_link');
}

