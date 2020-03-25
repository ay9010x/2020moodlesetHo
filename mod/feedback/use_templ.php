<?php



require_once("../../config.php");
require_once("lib.php");
require_once('use_templ_form.php');

$id = required_param('id', PARAM_INT);
$templateid = optional_param('templateid', false, PARAM_INT);

if (!$templateid) {
    redirect('edit.php?id='.$id);
}

$url = new moodle_url('/mod/feedback/use_templ.php', array('id'=>$id, 'templateid'=>$templateid));
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
$context = context_module::instance($cm->id);

require_login($course, true, $cm);

$feedback = $PAGE->activityrecord;
$feedbackstructure = new mod_feedback_structure($feedback, $cm, 0, $templateid);

require_capability('mod/feedback:edititems', $context);

$mform = new mod_feedback_use_templ_form();
$mform->set_data(array('id' => $id, 'templateid' => $templateid));

if ($mform->is_cancelled()) {
    redirect('edit.php?id='.$id.'&do_show=templates');
} else if ($formdata = $mform->get_data()) {
    feedback_items_from_template($feedback, $templateid, $formdata->deleteolditems);
    redirect('edit.php?id=' . $id);
}

$strfeedbacks = get_string("modulenameplural", "feedback");
$strfeedback  = get_string("modulename", "feedback");

navigation_node::override_active_url(new moodle_url('/mod/feedback/edit.php',
        array('id' => $id, 'do_show' => 'templates')));
$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($feedback->name));

echo $OUTPUT->heading(get_string('confirmusetemplate', 'feedback'), 4);

$mform->display();

$form = new mod_feedback_complete_form(mod_feedback_complete_form::MODE_VIEW_TEMPLATE,
        $feedbackstructure, 'feedback_preview_form', ['templateid' => $templateid]);
$form->display();

echo $OUTPUT->footer();

