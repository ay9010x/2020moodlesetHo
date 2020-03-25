<?php
 
require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/format/lib.php');
require_once('edit_form.php');
require('lib.php');
require_once('locallib.php');

$id       = required_param('id', PARAM_INT);

$PAGE->set_pagelayout('course');
$params = array('id'=>$id);
$PAGE->set_url('/local/syllabus_timeline/edit.php', $params);
$returnto = $CFG->wwwroot.'/local/syllabus_timeline/edit.php?id='.$id;

if ($id == SITEID){
    print_error('cannoteditsiteform');
}

$course = course_get_format($id)->get_course();
$context = context_course::instance($course->id);
require_login($course);
require_capability('moodle/course:update', $context);

$numsections = $DB->get_field('course_format_options','value', array('courseid'=>$course->id, 'name'=>'numsections'));
$sql = "SELECT section,topic,outline,talk,demo,homework,other,remark FROM {syllabus_timeline} WHERE course = :courseid AND section <= :numsections";
$timelines = $DB->get_records_sql($sql, array('courseid'=>$course->id, 'numsections'=>$numsections));
$tldata = new stdClass();
foreach($timelines as $tl){
    $section     = $tl->section;
    $topickey    = 'topic'.$section;
    $outlinekey  = 'outline'.$section;
    $talkkey     = 'talk'.$section;
    $demokey     = 'demo'.$section;
    $homeworkkey = 'homework'.$section;
    $otherkey    = 'other'.$section;
    $remarkkey   = 'remark'.$section;
    
    $tldata->$topickey    = $tl->topic;
    $tldata->$outlinekey  = $tl->outline;
    $tldata->$talkkey     = $tl->talk;
    $tldata->$demokey     = $tl->demo;
    $tldata->$homeworkkey = $tl->homework;
    $tldata->$otherkey    = $tl->other;
    $tldata->$remarkkey   = $tl->remark;
}

$editform = new local_syllabus_timeline_edit_info_form(NULL, array('data'=>$tldata, 'course'=>$course, 'numsections'=>$numsections ,'returnto' => $returnto));
if ($editform->is_cancelled()) {
    redirect(new moodle_url('/local/syllabus_timeline/index.php', array('id' => $course->id)));
} else if ($data = $editform->get_data()) {
    local_syllabus_timeline_update($data, $course->id);
    local_syllabus_timeline_standard_log_update($course);
    redirect(new moodle_url('/local/syllabus_timeline/index.php', array('id' => $course->id)));
}

$str_sesettings = get_string("editall", 'local_syllabus_timeline');
$PAGE->navbar->add(get_string('pluginname', 'local_syllabus_timeline'), new moodle_url($CFG->wwwroot.'/local/syllabus_timeline/index.php?id='.$course->id));
$PAGE->navbar->add($str_sesettings);
$PAGE->set_title($str_sesettings);
$PAGE->set_heading($str_sesettings);

echo $OUTPUT->header();
echo $OUTPUT->heading($str_sesettings);
$editform->display();
echo $OUTPUT->footer();