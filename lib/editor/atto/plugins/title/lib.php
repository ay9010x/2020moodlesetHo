<?php



defined('MOODLE_INTERNAL') || die();

function atto_title_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('h3',
                                          'h4',
                                          'h5',
                                          'pre',
                                          'p'), 'atto_title');
}

