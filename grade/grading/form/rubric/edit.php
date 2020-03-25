<?php



require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/edit_form.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');

$areaid = required_param('areaid', PARAM_INT);

$manager = get_grading_manager($areaid);

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);

require_login($course, true, $cm);
require_capability('moodle/grade:managegradingforms', $context);

$controller = $manager->get_controller('rubric');

$PAGE->set_url(new moodle_url('/grade/grading/form/rubric/edit.php', array('areaid' => $areaid)));
$PAGE->set_title(get_string('definerubric', 'gradingform_rubric'));
$PAGE->set_heading(get_string('definerubric', 'gradingform_rubric'));

$mform = new gradingform_rubric_editrubric(null, array('areaid' => $areaid, 'context' => $context, 'allowdraft' => !$controller->has_active_instances()), 'post', '', array('class' => 'gradingform_rubric_editform'));
$data = $controller->get_definition_for_editing(true);
$returnurl = optional_param('returnurl', $manager->get_management_url(), PARAM_LOCALURL);
$data->returnurl = $returnurl;
$mform->set_data($data);
if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($mform->is_submitted() && $mform->is_validated() && !$mform->need_confirm_regrading($controller)) {
        $data = $mform->get_data();
    $controller->update_definition($data);

        $warning = null;
    if (!empty($data->returnurl)) {
        if (($scores = $controller->get_min_max_score()) && $scores['minscore'] <> 0) {
            $warning = get_string('zerolevelsabsent', 'gradingform_rubric').'<br>'.
                html_writer::link($manager->get_management_url(), get_string('back'));
        }
    }
    redirect($returnurl, $warning, null, \core\output\notification::NOTIFY_ERROR);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();