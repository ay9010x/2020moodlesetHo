<?php



require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lti/lib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$status = optional_param('status', '', PARAM_TEXT);
$msg = optional_param('lti_msg', '', PARAM_TEXT);
$err = optional_param('lti_errormsg', '', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);

require_sesskey();
require_login(0, false);

$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

$pageurl = new moodle_url('/mod/lti/externalregistrationreturn.php');
$PAGE->set_context($systemcontext);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('maintenance');
$output = $PAGE->get_renderer('mod_lti');
echo $output->header();

if ($status !== 'success' && empty($err)) {
        if (!empty($msg)) {
                $err = $msg;
    } else {
                $err = get_string('failedtocreatetooltype', 'mod_lti');
    }
}
$params = array('message' => s($msg), 'error' => s($err), 'id' => $id, 'status' => s($status));

$page = new \mod_lti\output\external_registration_return_page();
echo $output->render($page);

$PAGE->requires->js_call_amd('mod_lti/external_registration_return', 'init', $params);
echo $output->footer();
