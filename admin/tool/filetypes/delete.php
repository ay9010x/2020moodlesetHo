<?php



require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('tool_filetypes');

$extension = required_param('extension', PARAM_ALPHANUMEXT);
$redirecturl = new \moodle_url('/admin/tool/filetypes/index.php');

if (optional_param('delete', 0, PARAM_INT)) {
    require_sesskey();

        core_filetypes::delete_type($extension);
    redirect($redirecturl);
}

$title = get_string('deletefiletypes', 'tool_filetypes');

$context = context_system::instance();
$PAGE->set_url(new \moodle_url('/admin/tool/filetypes/delete.php', array('extension' => $extension)));
$PAGE->navbar->add($title);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->fullname. ': ' . $title);

echo $OUTPUT->header();

$message = get_string('delete_confirmation', 'tool_filetypes', $extension);
$deleteurl = new \moodle_url('delete.php', array('extension' => $extension, 'delete' => 1));
$yesbutton = new single_button($deleteurl, get_string('yes'));
$nobutton = new single_button($redirecturl, get_string('no'), 'get');
echo $OUTPUT->confirm($message, $yesbutton, $nobutton);

echo $OUTPUT->footer();
