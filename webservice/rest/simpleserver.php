<?php





define('NO_DEBUG_DISPLAY', true);

define('WS_SERVER', true);

require('../../config.php');
require_once("$CFG->dirroot/webservice/rest/locallib.php");

if (!webservice_protocol_is_enabled('rest')) {
    die;
}

$restformat = optional_param('moodlewsrestformat', 'xml', PARAM_ALPHA);
if (isset($_REQUEST['moodlewsrestformat'])) {
    unset($_REQUEST['moodlewsrestformat']);
}
if (isset($_GET['moodlewsrestformat'])) {
    unset($_GET['moodlewsrestformat']);
}
if (isset($_POST['moodlewsrestformat'])) {
    unset($_POST['moodlewsrestformat']);
}

$server = new webservice_rest_server(WEBSERVICE_AUTHMETHOD_USERNAME);
$server->run();
die;


