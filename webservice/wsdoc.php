<?php




require_once('../config.php');
require($CFG->dirroot . '/webservice/lib.php');

require_login();
require_sesskey();

$usercontext = context_user::instance($USER->id);
$tokenid = required_param('id', PARAM_INT);

$PAGE->set_context($usercontext);
$PAGE->set_url('/user/wsdoc.php');
$PAGE->set_title(get_string('documentation', 'webservice'));
$PAGE->set_heading(get_string('documentation', 'webservice'));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->ignore_active(true);
$PAGE->navbar->add(get_string('usercurrentsettings'));
$PAGE->navbar->add(get_string('securitykeys', 'webservice'),
        new moodle_url('/user/managetoken.php', 
                array('id' => $tokenid, 'sesskey' => sesskey())));
$PAGE->navbar->add(get_string('documentation', 'webservice'));

if (empty($CFG->enablewsdocumentation)) {
    echo get_string('wsdocumentationdisable', 'webservice');
    die;
}

$webservice = new webservice();
$token = $webservice->get_token_by_id($tokenid);
if (empty($token) or empty($token->userid) or empty($USER->id)
        or ($token->userid != $USER->id)) {
    throw new moodle_exception('docaccessrefused', 'webservice');
}

$functions = $webservice->get_external_functions(array($token->externalserviceid));

$functiondescs = array();
foreach ($functions as $function) {
    $functiondescs[$function->name] = external_api::external_function_info($function);
}

$activatedprotocol = array();
$activatedprotocol['rest'] = webservice_protocol_is_enabled('rest');
$activatedprotocol['xmlrpc'] = webservice_protocol_is_enabled('xmlrpc');

$printableformat = optional_param('print', false, PARAM_BOOL);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('core', 'webservice');
echo $renderer->documentation_html($functiondescs,
        $printableformat, $activatedprotocol, array('id' => $tokenid));

if (!empty($printableformat)) {
    $PAGE->requires->js_function_call('window.print', array());
}

echo $OUTPUT->footer();
