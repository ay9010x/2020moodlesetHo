<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once('locallib.php');

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
$PAGE->set_pagelayout('course');

$cmid = block_course_menu_get_course_attendance($course);
$context = CONTEXT_COURSE::instance($course->id, MUST_EXIST);
$switchrole='';
if(!empty($USER->access['rsw'][$context->path])){
    $switchrole = $USER->access['rsw'][$context->path];
}
if((has_capability('moodle/course:update', $context) && $switchrole == '') || has_capability('moodle/course:bulkmessaging', $context)){    $url = $CFG->wwwroot.'/mod/attendance/manage.php?id='.$cmid;
}else{
    $url = $CFG->wwwroot.'/mod/attendance/view.php?id='.$cmid;
}
redirect($url);