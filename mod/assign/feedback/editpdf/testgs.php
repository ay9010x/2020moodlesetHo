<?php



require('../../../../config.php');

global $PAGE, $OUTPUT;

$PAGE->set_url(new moodle_url('/mod/assign/feedback/editpdf/testgs.php'));
$PAGE->set_context(context_system::instance());

require_login();
require_capability('moodle/site:config', context_system::instance());

$strheading = get_string('testgs', 'assignfeedback_editpdf');
$PAGE->navbar->add(get_string('administrationsite'));
$PAGE->navbar->add(get_string('plugins', 'admin'));
$PAGE->navbar->add(get_string('assignmentplugins', 'mod_assign'));
$PAGE->navbar->add(get_string('feedbackplugins', 'mod_assign'));
$PAGE->navbar->add(get_string('pluginname', 'assignfeedback_editpdf'), new moodle_url('/admin/settings.php?section=assignfeedback_editpdf'));
$PAGE->navbar->add($strheading);
$PAGE->set_heading($strheading);
$PAGE->set_title($strheading);

if (optional_param('sendimage', false, PARAM_BOOL)) {
        assignfeedback_editpdf\pdf::send_test_image();
    die();
}

$result = assignfeedback_editpdf\pdf::test_gs_path();

switch ($result->status) {
    case assignfeedback_editpdf\pdf::GSPATH_OK:
        $msg = get_string('test_ok', 'assignfeedback_editpdf');
        $msg .= html_writer::empty_tag('br');
        $imgurl = new moodle_url($PAGE->url, array('sendimage' => 1));
        $msg .= html_writer::empty_tag('img', array('src' => $imgurl, 'alt' => get_string('gsimage', 'assignfeedback_editpdf')));
        break;

    case assignfeedback_editpdf\pdf::GSPATH_ERROR:
        $msg = $result->message;
        break;

    default:
        $msg = get_string("test_{$result->status}", 'assignfeedback_editpdf');
        break;
}

$returl = new moodle_url('/admin/settings.php', array('section' => 'assignfeedback_editpdf'));
$msg .= $OUTPUT->continue_button($returl);

echo $OUTPUT->header();
echo $OUTPUT->box($msg, 'generalbox ');
echo $OUTPUT->footer();
