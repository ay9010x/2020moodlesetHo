<?php



require('../../config.php');
require_once($CFG->dirroot . '/mod/imscp/lib.php');
require_once("$CFG->dirroot/mod/imscp/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);  $i  = optional_param('i', 0, PARAM_INT);   
if ($i) {      $imscp = $DB->get_record('imscp', array('id' => $i), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('imscp', $imscp->id, $imscp->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('imscp', $id, 0, false, MUST_EXIST);
    $imscp = $DB->get_record('imscp', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/imscp:view', $context);

imscp_view($imscp, $course, $cm, $context);

$PAGE->set_url('/mod/imscp/view.php', array('id' => $cm->id));
$PAGE->requires->js('/mod/imscp/dummyapi.js', true);

$PAGE->requires->string_for_js('navigation', 'imscp');
$PAGE->requires->string_for_js('toc', 'imscp');
$PAGE->requires->string_for_js('hide', 'moodle');
$PAGE->requires->string_for_js('show', 'moodle');


$PAGE->set_title($course->shortname.': '.$imscp->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($imscp);

if (!$imscp->structure) {
    redirect(course_get_url($course->id, $cm->section), get_string('deploymenterror', 'imscp'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($imscp->name));

imscp_print_content($imscp, $cm, $course);

echo $OUTPUT->footer();
