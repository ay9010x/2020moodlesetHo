<?php



require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$name   = required_param('name', PARAM_PLUGIN);

$syscontext = context_system::instance();
$PAGE->set_url('/admin/dataformats.php');
$PAGE->set_context($syscontext);

require_login();
require_capability('moodle/site:config', $syscontext);
require_sesskey();

$return = new moodle_url('/admin/settings.php', array('section' => 'managedataformats'));

$plugins = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
$sortorder = array_flip(array_keys($plugins));

if (!isset($plugins[$name])) {
    print_error('courseformatnotfound', 'error', $return, $name);
}

switch ($action) {
    case 'disable':
        if ($plugins[$name]->is_enabled()) {
            set_config('disabled', 1, 'dataformat_'. $name);
            core_plugin_manager::reset_caches();
        }
        break;
    case 'enable':
        if (!$plugins[$name]->is_enabled()) {
            unset_config('disabled', 'dataformat_'. $name);
            core_plugin_manager::reset_caches();
        }
        break;
    case 'up':
        if ($sortorder[$name]) {
            $currentindex = $sortorder[$name];
            $seq = array_keys($plugins);
            $seq[$currentindex] = $seq[$currentindex - 1];
            $seq[$currentindex - 1] = $name;
            set_config('dataformat_plugins_sortorder', implode(',', $seq));
        }
        break;
    case 'down':
        if ($sortorder[$name] < count($sortorder) - 1) {
            $currentindex = $sortorder[$name];
            $seq = array_keys($plugins);
            $seq[$currentindex] = $seq[$currentindex + 1];
            $seq[$currentindex + 1] = $name;
            set_config('dataformat_plugins_sortorder', implode(',', $seq));
        }
        break;
}
redirect($return);

