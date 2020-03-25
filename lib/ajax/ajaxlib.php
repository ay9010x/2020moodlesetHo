<?php





function user_preference_allow_ajax_update($name, $paramtype) {
    global $USER, $PAGE;

        $USER->ajax_updatable_user_prefs[$name] = $paramtype;
}


function ajax_capture_output() {
        return ob_start();
}


function ajax_check_captured_output() {
    global $CFG;

        $output = ob_get_contents();
    ob_end_clean();

    if (!empty($output)) {
        $message = 'Unexpected output whilst processing AJAX request. ' .
                'This could be caused by trailing whitespace. Output received: ' .
                var_export($output, true);
        if ($CFG->debugdeveloper && !empty($output)) {
                        throw new coding_exception($message);
        }
        error_log('Potential coding error: ' . $message);
    }
    return $output;
}
