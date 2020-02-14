<?php



require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/edit_form.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');

$areaid = required_param('areaid', PARAM_INT);

$manager = get_grading_manager($areaid);

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);

require_login($course, true, $cm);

$controller = $manager->get_controller('guide');
$options = $controller->get_options();

if (!$controller->is_form_defined() || empty($options['alwaysshowdefinition'])) {
    throw new moodle_exception('nopermissions', 'error', '', get_string('previewmarkingguide', 'gradingform_guide'));
}

$title = get_string('gradingof', 'gradingform_guide', $manager->get_area_title());
$PAGE->set_url(new moodle_url('/grade/grading/form/guide/preview.php', array('areaid' => $areaid)));
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo $controller->render_preview($PAGE);
echo $OUTPUT->footer();
