<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

$context = context_course::instance($course->id);

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

$course = course_get_format($course)->get_course();
if($course->id != SITEID){
  course_create_sections_if_missing($course, range(0, $course->numsections));
}
$renderer = $PAGE->get_renderer('block_course_menu');
$renderer->print_multiple_section_page($course, null, null, null, null, 'forum');

$PAGE->requires->js('/blocks/course_menu/format/forum/format.js');