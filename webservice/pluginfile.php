<?php





define('AJAX_SCRIPT', true);


define('NO_MOODLE_COOKIES', true);


require_once(dirname(dirname(__FILE__)) . '/config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

header('Access-Control-Allow-Origin: *');

$token = required_param('token', PARAM_ALPHANUM);
$webservicelib = new webservice();
$authenticationinfo = $webservicelib->authenticate_user($token);

$enabledfiledownload = (int) ($authenticationinfo['service']->downloadfiles);
if (empty($enabledfiledownload)) {
    throw new webservice_access_exception('Web service file downloading must be enabled in external service settings');
}

$relativepath = get_file_argument();
file_pluginfile($relativepath, 0);
