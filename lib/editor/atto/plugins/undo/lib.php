<?php



defined('MOODLE_INTERNAL') || die();


function atto_undo_strings_for_js() {
    global $PAGE;

            $PAGE->requires->strings_for_js(
        array(
            'redo',
            'undo'
        ),
        'atto_undo'
    );
}
