<?php



defined('MOODLE_INTERNAL') || die();


function atto_indent_strings_for_js() {
    global $PAGE;
    $PAGE->requires->strings_for_js(array('indent', 'outdent'), 'atto_indent');
}
