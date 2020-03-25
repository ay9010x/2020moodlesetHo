<?php



require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('tool_filetypes');

$extension = required_param('extension', PARAM_RAW);
$redirecturl = new \moodle_url('/admin/tool/filetypes/index.php');

if (optional_param('revert', 0, PARAM_INT)) {
    require_sesskey();

        core_filetypes::revert_type_to_default($extension);
    redirect($redirecturl);
}

$title = get_string('revertfiletype', 'tool_filetypes');

$context = context_system::instance();
$PAGE->set_url(new \moodle_url('/admin/tool/filetypes/revert.php', array('extension' => $extension)));
$PAGE->navbar->add($title);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname. ': ' . $title);

echo $OUTPUT->header();

$message = get_string('revert_confirmation', 'tool_filetypes', $extension);
$reverturl = new \moodle_url('revert.php', array('extension' => $extension, 'revert' => 1));
$yesbutton = new single_button($reverturl, get_string('yes'));
$nobutton = new single_button($redirecturl, get_string('no'), 'get');
echo $OUTPUT->confirm($message, $yesbutton, $nobutton);

echo $OUTPUT->footer();
