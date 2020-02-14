<?php



defined('MOODLE_INTERNAL') || die();


function atto_collapse_strings_for_js() {
    global $PAGE;

    $PAGE->requires->strings_for_js(array('showmore', 'showfewer'), 'atto_collapse');
}


function atto_collapse_params_for_js($elementid, $options, $fpoptions) {
        $params = array('showgroups' => get_config('atto_collapse', 'showgroups'));
    return $params;
}
