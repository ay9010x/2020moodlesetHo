<?php



define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');


$response = new \mod_lti\local\ltiservice\response();

$isget = $response->get_request_method() == 'GET';

if ($isget) {
    $response->set_accept(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '');
} else {
    $response->set_content_type(isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '');
}

$ok = false;
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

$accept = $response->get_accept();

$services = lti_get_services();
foreach ($services as $service) {
    $resources = $service->get_resources();
    foreach ($resources as $resource) {
        if (($isget && !empty($accept) && (strpos($accept, '*/*') === false) &&
             !in_array($accept, $resource->get_formats())) ||
            (!$isget && !in_array($response->get_content_type(), $resource->get_formats()))) {
            continue;
        }
        $template = $resource->get_template();
        $template = preg_replace('/\{[a-zA-Z_]+\}/', '[^/]+', $template);
        $template = preg_replace('/\{\?[0-9a-zA-Z_\-,]+\}$/', '', $template);
        $template = str_replace('/', '\/', $template);
        if (preg_match("/{$template}/", $path) === 1) {
            $ok = true;
            break 2;
        }
    }
}
if (!$ok) {
    $response->set_code(400);
} else {
    $body = file_get_contents('php://input');
    $response->set_request_data($body);
    if (in_array($response->get_request_method(), $resource->get_methods())) {
        $resource->execute($response);
    } else {
        $response->set_code(405);
    }
}
$response->send();
