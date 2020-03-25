<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

$id = optional_param('id', 0, PARAM_INT);

$url = new moodle_url('/blocks/course_menu/courses_news.php', array('id'=>$id));
$PAGE->set_url($url);

if ($id) {
    if (! $course = $DB->get_record('course', array('id' => $id))) {
        print_error('invalidcourseid');
    }
} else {
    $course = get_site();
}

require_login($course->id);
//$PAGE->set_pagelayout('course');   // by YCJ

$viewing = 'course_'.$course->id;
$url = $CFG->wwwroot.'/message/index.php?user1='.$USER->id.'&viewing='.$viewing;
redirect($url);