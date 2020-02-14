<?php
  
require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/format/lib.php');
require_once('edit_form.php');
require('lib.php');
require_once('locallib.php');

$id       = required_param('id', PARAM_INT);

$PAGE->set_pagelayout('course');
$params = array('id'=>$id);
$PAGE->set_url('/local/syllabus_week/edit.php', $params);
$returnto = $CFG->wwwroot.'/local/syllabus_week/edit.php?id='.$id;

if ($id == SITEID){
    print_error('cannoteditsiteform');
}

$course = course_get_format($id)->get_course();
$context = context_course::instance($course->id);
require_login($course);
require_capability('moodle/course:update', $context);

$numsections = $DB->get_field('course_format_options','value', array('courseid'=>$course->id, 'name'=>'numsections'));
$sql = "SELECT section,course,date,week,session,location,summary FROM {syllabus_week} WHERE course = :courseid AND section <= :numsections";
$weeks = $DB->get_records_sql($sql, array('courseid'=>$course->id, 'numsections'=>$numsections));
$weekdata = new stdClass();
foreach($weeks as $week){
    $section = $week->section;
    $datekey = 'date'.$section;
    $weekkey = 'week'.$section;
    $sessionkey = 'session'.$section;
    $locationkey = 'location'.$section;
    $summarykey = 'summary'.$section;
    
    $weekdata->$datekey = $week->date;
    $weekdata->$weekkey = $week->week;
    $weekdata->$sessionkey = $week->session;
    $weekdata->$locationkey = $week->location;
    $weekdata->$summarykey = $week->summary;
}

$editform = new local_syllabus_week_edit_info_form(NULL, array('data'=>$weekdata, 'course'=>$course, 'numsections'=>$numsections ,'returnto' => $returnto));
if ($editform->is_cancelled()) {
    redirect(new moodle_url('/local/syllabus_week/index.php', array('id' => $course->id)));
} else if ($data = $editform->get_data()) {
    local_syllabus_week_update($data, $course->id);
    local_syllabus_week_standard_log_update($course);
    redirect(new moodle_url('/local/syllabus_week/index.php', array('id' => $course->id)));
}

$str_sesettings = get_string("editall", 'local_syllabus_week');
$PAGE->navbar->add(get_string('pluginname', 'local_syllabus_week'), new moodle_url($CFG->wwwroot.'/local/syllabus_week/index.php?id='.$course->id));
$PAGE->navbar->add($str_sesettings);
$PAGE->set_title($str_sesettings);
$PAGE->set_heading($str_sesettings);

echo $OUTPUT->header();
echo $OUTPUT->heading($str_sesettings);
$editform->display();
echo $OUTPUT->footer();