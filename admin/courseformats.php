<?php



require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$action  = required_param('action', PARAM_ALPHANUMEXT);
$formatname   = required_param('format', PARAM_PLUGIN);

$syscontext = context_system::instance();
$PAGE->set_url('/admin/courseformats.php');
$PAGE->set_context($syscontext);

require_login();
require_capability('moodle/site:config', $syscontext);
require_sesskey();

$return = new moodle_url('/admin/settings.php', array('section' => 'manageformats'));

$formatplugins = core_plugin_manager::instance()->get_plugins_of_type('format');
$sortorder = array_flip(array_keys($formatplugins));

if (!isset($formatplugins[$formatname])) {
    print_error('courseformatnotfound', 'error', $return, $formatname);
}

switch ($action) {
    case 'disable':
        if ($formatplugins[$formatname]->is_enabled()) {
            if (get_config('moodlecourse', 'format') === $formatname) {
                print_error('cannotdisableformat', 'error', $return);
            }
            set_config('disabled', 1, 'format_'. $formatname);
            core_plugin_manager::reset_caches();
        }
        break;
    case 'enable':
        if (!$formatplugins[$formatname]->is_enabled()) {
            unset_config('disabled', 'format_'. $formatname);
            core_plugin_manager::reset_caches();
        }
        break;
    case 'up':
        if ($sortorder[$formatname]) {
            $currentindex = $sortorder[$formatname];
            $seq = array_keys($formatplugins);
            $seq[$currentindex] = $seq[$currentindex-1];
            $seq[$currentindex-1] = $formatname;
            set_config('format_plugins_sortorder', implode(',', $seq));
        }
        break;
    case 'down':
        if ($sortorder[$formatname] < count($sortorder)-1) {
            $currentindex = $sortorder[$formatname];
            $seq = array_keys($formatplugins);
            $seq[$currentindex] = $seq[$currentindex+1];
            $seq[$currentindex+1] = $formatname;
            set_config('format_plugins_sortorder', implode(',', $seq));
        }
        break;
}
redirect($return);
