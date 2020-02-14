<?php




define('AJAX_SCRIPT', true);

require(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

if (!has_capability('moodle/site:config', context_system::instance())) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

if (!empty($CFG->disableupdateautodeploy)) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

if (!confirm_sesskey()) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

$plugintype = optional_param('plugintype', null, PARAM_ALPHANUMEXT);
if (is_null($plugintype)) {
    header('HTTP/1.1 400 Bad Request');
    die();
}

$pluginman = core_plugin_manager::instance();

$plugintypepath = $pluginman->get_plugintype_root($plugintype);

if (empty($plugintypepath)) {
    header('HTTP/1.1 400 Bad Request');
    die();
}

$response = array('path' => $plugintypepath);

if ($pluginman->is_plugintype_writable($plugintype)) {
    $response['writable'] = 1;
} else {
    $response['writable'] = 0;
}

header('Content-Type: application/json; charset: utf-8');
echo json_encode($response);
