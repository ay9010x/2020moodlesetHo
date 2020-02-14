<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/assign/adminlib.php');

$subtype = required_param('subtype', PARAM_PLUGIN);
$action = optional_param('action', null, PARAM_PLUGIN);
$plugin = optional_param('plugin', null, PARAM_PLUGIN);

if (!empty($plugin)) {
    require_sesskey();
}

$pluginmanager = new assign_plugin_manager($subtype);

$PAGE->set_context(context_system::instance());

$pluginmanager->execute($action, $plugin);
