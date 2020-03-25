<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot .'/course/renderer.php');
require_once($CFG->dirroot .'/local/mooccourse/renderer.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);
if (!$course = $DB->get_record("course", array("id"=>$id))) {
    print_error("invalidcourseid");
}

$context = context_course::instance($course->id);
$PAGE->set_course($course);
$PAGE->set_pagelayout('base');
$PAGE->set_url('/blocks/course_menu/information.php', array('id' => $course->id));
$PAGE->set_title(get_string("summaryof", "", $course->fullname));
$PAGE->set_heading(get_string('courseinfo'));
$PAGE->navbar->add(get_string('summary'));
echo $OUTPUT->header();

if ($texts = enrol_get_course_description_texts($course)) {
    echo $OUTPUT->box_start('generalbox icons');
    echo implode($texts);
    echo $OUTPUT->box_end();
}    

$renderer = $PAGE->get_renderer('local_mooccourse');
echo $renderer->local_mooccourse_course_info_item($course);   
echo "<br />";
                   
echo $renderer->local_mooccourse_course_info_detail($course); 
echo $OUTPUT->footer();