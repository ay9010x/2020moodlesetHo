<?php





define('NO_DEBUG_DISPLAY', true);

define('WS_SERVER', true);

require('../../config.php');
require_once("$CFG->dirroot/webservice/xmlrpc/locallib.php");

if (!webservice_protocol_is_enabled('xmlrpc')) {
    debugging('The server died because the web services or the XMLRPC protocol are not enable',
        DEBUG_DEVELOPER);
    die;
}

$server = new webservice_xmlrpc_server(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);
$server->run();
die;

