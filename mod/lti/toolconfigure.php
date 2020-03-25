<?php



require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/lti/lib.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$cartridgeurl = optional_param('cartridgeurl', '', PARAM_URL);

require_login(0, false);
admin_externalpage_setup('ltitoolconfigure');

if ($cartridgeurl) {
    $type = new stdClass();
    $data = new stdClass();
    $type->state = LTI_TOOL_STATE_CONFIGURED;
    $data->lti_coursevisible = 1;
    lti_load_type_from_cartridge($cartridgeurl, $data);
    lti_add_type($type, $data);
}

$pageurl = new moodle_url('/mod/lti/toolconfigure.php');
$PAGE->set_url($pageurl);
$PAGE->set_title("{$SITE->shortname}: " . get_string('toolregistration', 'mod_lti'));
$PAGE->requires->string_for_js('success', 'moodle');
$PAGE->requires->string_for_js('error', 'moodle');
$PAGE->requires->string_for_js('successfullycreatedtooltype', 'mod_lti');
$PAGE->requires->string_for_js('failedtocreatetooltype', 'mod_lti');
$output = $PAGE->get_renderer('mod_lti');

echo $output->header();

$page = new \mod_lti\output\tool_configure_page();
echo $output->render($page);

echo $output->footer();
