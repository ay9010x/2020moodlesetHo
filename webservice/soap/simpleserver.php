<?php





define('NO_DEBUG_DISPLAY', true);

define('WS_SERVER', true);

require('../../config.php');
require_once("$CFG->dirroot/webservice/soap/locallib.php");

if (!webservice_protocol_is_enabled('soap')) {
    die;
}

$server = new webservice_soap_server(WEBSERVICE_AUTHMETHOD_USERNAME);
$server->run();
die;


