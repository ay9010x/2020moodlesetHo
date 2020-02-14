<?php



define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/externallib.php');

define('PREFERRED_RENDERER_TARGET', RENDERER_TARGET_GENERAL);

$rawjson = file_get_contents('php://input');

$requests = json_decode($rawjson, true);
if ($requests === null) {
    if (function_exists('json_last_error_msg')) {
        $lasterror = json_last_error_msg();
    } else {
                $lasterror = json_last_error();
    }
    throw new coding_exception('Invalid json in request: ' . $lasterror);
}
$responses = array();

$settings = external_settings::get_instance();
$settings->set_file('pluginfile.php');
$settings->set_fileurl(true);
$settings->set_filter(true);
$settings->set_raw(false);

foreach ($requests as $request) {
    $response = array();
    $methodname = clean_param($request['methodname'], PARAM_ALPHANUMEXT);
    $index = clean_param($request['index'], PARAM_INT);
    $args = $request['args'];

    $response = external_api::call_external_function($methodname, $args, true);
    $responses[$index] = $response;
    if ($response['error']) {
                break;
    }
}

echo json_encode($responses);
