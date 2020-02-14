<?php



require_once(__DIR__ . "/../../config.php");
require_once($CFG->dirroot . "/mod/feedback/lib.php");
require_once("$CFG->libdir/tablelib.php");

$id = required_param('id', PARAM_INT); 
$url = new moodle_url('/mod/feedback/mapcourse.php', array('id'=>$id));
$PAGE->set_url($url);

$current_tab = 'mapcourse';

list($course, $cm) = get_course_and_cm_from_cmid($id, 'feedback');
require_login($course, true, $cm);
$feedback = $PAGE->activityrecord;

$context = context_module::instance($cm->id);
require_capability('mod/feedback:mapcourse', $context);

$coursemap = array_keys(feedback_get_courses_from_sitecourse_map($feedback->id));
$form = new mod_feedback_course_map_form();
$form->set_data(array('id' => $cm->id, 'mappedcourses' => $coursemap));
$mainurl = new moodle_url('/mod/feedback/view.php', ['id' => $id]);
if ($form->is_cancelled()) {
    redirect($mainurl);
} else if ($data = $form->get_data()) {
    feedback_update_sitecourse_map($feedback, $data->mappedcourses);
    redirect($mainurl, get_string('mappingchanged', 'feedback'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$strfeedbacks = get_string("modulenameplural", "feedback");
$strfeedback  = get_string("modulename", "feedback");

$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedback->name));

require('tabs.php');

echo $OUTPUT->box(get_string('mapcourseinfo', 'feedback'));

$form->display();

echo $OUTPUT->footer();
