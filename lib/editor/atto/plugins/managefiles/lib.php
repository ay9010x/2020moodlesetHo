<?php



defined('MOODLE_INTERNAL') || die();


function atto_managefiles_strings_for_js() {
    global $PAGE;
    $PAGE->requires->strings_for_js(array('managefiles'), 'atto_managefiles');
}


function atto_managefiles_params_for_js($elementid, $options, $fpoptions) {
    global $CFG, $USER;
    require_once($CFG->dirroot . '/repository/lib.php');  
                    $disabled = !isloggedin() || isguestuser() ||
            (!isset($options['maxfiles']) || $options['maxfiles'] == 0) ||
            (isset($options['return_types']) && !($options['return_types'] & ~FILE_EXTERNAL));

    $params = array('disabled' => $disabled, 'area' => array(), 'usercontext' => null);

    if (!$disabled) {
        $params['usercontext'] = context_user::instance($USER->id)->id;
        foreach (array('itemid', 'context', 'areamaxbytes', 'maxbytes', 'subdirs', 'return_types') as $key) {
            if (isset($options[$key])) {
                if ($key === 'context' && is_object($options[$key])) {
                                        $params['area'][$key] = $options[$key]->id;
                } else {
                    $params['area'][$key] = $options[$key];
                }
            }
        }
    }

    return $params;
}
