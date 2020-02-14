<?php



defined('MOODLE_INTERNAL') || die();


function atto_align_strings_for_js() {
    global $PAGE;
    $PAGE->requires->strings_for_js(array('center', 'leftalign', 'rightalign'), 'atto_align');
}
