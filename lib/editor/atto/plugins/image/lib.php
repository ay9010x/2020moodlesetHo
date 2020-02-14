<?php



defined('MOODLE_INTERNAL') || die();


function atto_image_strings_for_js() {
    global $PAGE;

    $strings = array(
        'alignment',
        'alignment_bottom',
        'alignment_left',
        'alignment_middle',
        'alignment_right',
        'alignment_top',
        'browserepositories',
        'constrain',
        'saveimage',
        'imageproperties',
        'customstyle',
        'enterurl',
        'enteralt',
        'height',
        'presentation',
        'presentationoraltrequired',
        'size',
        'width',
        'uploading',
    );

    $PAGE->requires->strings_for_js($strings, 'atto_image');
}

