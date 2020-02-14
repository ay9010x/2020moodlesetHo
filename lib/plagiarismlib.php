<?php





if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }


function plagiarism_get_links($linkarray) {
    global $CFG;
    if (empty($CFG->enableplagiarism)) {
        return '';
    }
    $plagiarismplugins = plagiarism_load_available_plugins();
    $output = '';
    foreach($plagiarismplugins as $plugin => $dir) {
        require_once($dir.'/lib.php');
        $plagiarismclass = "plagiarism_plugin_$plugin";
        $plagiarismplugin = new $plagiarismclass;
        $output .= $plagiarismplugin->get_links($linkarray);
    }
    return $output;
}


function plagiarism_get_file_results($cmid, $userid, $file) {
    global $CFG;
    $allresults = array();
    if (empty($CFG->enableplagiarism)) {
        return $allresults;
    }
    $plagiarismplugins = plagiarism_load_available_plugins();
    foreach($plagiarismplugins as $plugin => $dir) {
        require_once($dir.'/lib.php');
        $plagiarismclass = "plagiarism_plugin_$plugin";
        $plagiarismplugin = new $plagiarismclass;
        $allresults[] = $plagiarismplugin->get_file_results($cmid, $userid, $file);
    }
    return $allresults;
}


function plagiarism_save_form_elements($data) {
    global $CFG;
    if (empty($CFG->enableplagiarism)) {
        return '';
    }
    $plagiarismplugins = plagiarism_load_available_plugins();
    foreach($plagiarismplugins as $plugin => $dir) {
        require_once($dir.'/lib.php');
        $plagiarismclass = "plagiarism_plugin_$plugin";
        $plagiarismplugin = new $plagiarismclass;
        $plagiarismplugin->save_form_elements($data);
    }
}


function plagiarism_get_form_elements_module($mform, $context, $modulename = "") {
    global $CFG;
    if (empty($CFG->enableplagiarism)) {
        return '';
    }
    $plagiarismplugins = plagiarism_load_available_plugins();
    foreach($plagiarismplugins as $plugin => $dir) {
        require_once($dir.'/lib.php');
        $plagiarismclass = "plagiarism_plugin_$plugin";
        $plagiarismplugin = new $plagiarismclass;
        $plagiarismplugin->get_form_elements_module($mform, $context, $modulename);
    }
}

function plagiarism_update_status($course, $cm) {
    global $CFG;
    if (empty($CFG->enableplagiarism)) {
        return '';
    }
    $plagiarismplugins = plagiarism_load_available_plugins();
    $output = '';
    foreach($plagiarismplugins as $plugin => $dir) {
        require_once($dir.'/lib.php');
        $plagiarismclass = "plagiarism_plugin_$plugin";
        $plagiarismplugin = new $plagiarismclass;
        $output .= $plagiarismplugin->update_status($course, $cm);
    }
    return $output;
}


function plagiarism_print_disclosure($cmid) {
    global $CFG;
    if (empty($CFG->enableplagiarism)) {
        return '';
    }
    $plagiarismplugins = plagiarism_load_available_plugins();
    $output = '';
    foreach($plagiarismplugins as $plugin => $dir) {
        require_once($dir.'/lib.php');
        $plagiarismclass = "plagiarism_plugin_$plugin";
        $plagiarismplugin = new $plagiarismclass;
        $output .= $plagiarismplugin->print_disclosure($cmid);
    }
    return $output;
}

function plagiarism_cron() {
    global $CFG;
    if (empty($CFG->enableplagiarism)) {
        return '';
    }
    $plagiarismplugins = plagiarism_load_available_plugins();
    foreach($plagiarismplugins as $plugin => $dir) {
        require_once($dir.'/lib.php');
        $plagiarismclass = "plagiarism_plugin_$plugin";
        $plagiarismplugin = new $plagiarismclass;
        if (method_exists($plagiarismplugin, 'cron')) {
            mtrace('Processing cron function for plagiarism_plugin_' . $plugin . '...', '');
            cron_trace_time_and_memory();
            $plagiarismplugin->cron();
        }
    }
}

function plagiarism_load_available_plugins() {
    global $CFG;
    if (empty($CFG->enableplagiarism)) {
        return array();
    }
    $plagiarismplugins = core_component::get_plugin_list('plagiarism');
    $availableplugins = array();
    foreach($plagiarismplugins as $plugin => $dir) {
                if (get_config('plagiarism', $plugin."_use") && file_exists($dir."/lib.php")) {
            require_once($dir.'/lib.php');
            $plagiarismclass = "plagiarism_plugin_$plugin";
            if (class_exists($plagiarismclass)) {
                $availableplugins[$plugin] = $dir;
            }
        }
    }
    return $availableplugins;
}
