<?php



require_once("../../config.php");
require_once($CFG->dirroot.'/mod/lti/lib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$id = required_param('id', PARAM_INT); 
$toolproxy = $DB->get_record('lti_tool_proxies', array('id' => $id), '*', MUST_EXIST);

require_login(0, false);
require_sesskey();

$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

lti_register($toolproxy);
