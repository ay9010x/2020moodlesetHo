<?php



defined('MOODLE_INTERNAL') || die();


function atto_rtl_strings_for_js() {
    global $PAGE;

            $PAGE->requires->strings_for_js(
        array(
            'rtl',
            'ltr'
        ),
        'atto_rtl'
    );
}
