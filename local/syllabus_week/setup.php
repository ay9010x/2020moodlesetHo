<?php
  
require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/format/lib.php');
require_once('setup_form.php');
require('lib.php');
require_once('locallib.php');

$id       = required_param('id', PARAM_INT);

$PAGE->set_pagelayout('course');
$params = array('id'=>$id);
$PAGE->set_url('/local/syllabus_week/setup.php', $params);
$returnto = $CFG->wwwroot.'/local/syllabus_week/setup.php?id='.$id;

if ($id == SITEID){
    print_error('cannoteditsiteform');
}

$course = course_get_format($id)->get_course();
$context = context_course::instance($course->id);
require_login($course);
require_capability('moodle/course:update', $context);

course_create_syllabus_week_setting_if_missing($course->id);

$settings = $DB->get_record('syllabus_week_setting', array('course'=>$course->id));
unset($settings->id);
$editform = new local_syllabus_week_setup_edit_info_form(NULL, array('data'=>$settings, 'course'=>$course, 'returnto' => $returnto));
if ($editform->is_cancelled()) {
    redirect(new moodle_url('/local/syllabus_week/index.php', array('id' => $course->id)));
} else if ($data = $editform->get_data()) {
    local_syllabus_week_setup_update($data);
    local_syllabus_week_setup_standard_log_update($course);
    redirect(new moodle_url('/local/syllabus_week/index.php', array('id' => $course->id)));
}

$str_sesettings = get_string("setup", 'local_syllabus_week');
$PAGE->navbar->add(get_string('pluginname', 'local_syllabus_week'), new moodle_url($CFG->wwwroot.'/local/syllabus_week/index.php?id='.$course->id));
$PAGE->navbar->add($str_sesettings);
$title = $str_sesettings;
$fullname = $course->fullname;
$PAGE->set_title($title);
$PAGE->set_heading($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($str_sesettings);
$editform->display();
echo $OUTPUT->footer();