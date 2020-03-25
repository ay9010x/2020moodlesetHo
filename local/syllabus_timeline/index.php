<?php
/**
 * Version details.
 *
 * @package    local
 * @subpackage syllabus_timeline
 * @copyright  2017 Click-AP <mary@click-ap.com>
 * @license    http://www.click-ap.com/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__ . '/../../config.php');
require('lib.php');
require('locallib.php');

$id = required_param('id', PARAM_INT);
if (!$course = $DB->get_record("course", array("id"=>$id))) {
    print_error("invalidcourseid");
}
$context = context_course::instance($course->id);
require_login($course);

$PAGE->set_course($course);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/local/syllabus_timeline/index.php', array('id' => $course->id));
$PAGE->navbar->add(get_string('pluginname', 'local_syllabus_timeline'));
$PAGE->set_title(get_string('pluginname', 'local_syllabus_timeline'));
$PAGE->set_heading(get_string('pluginname', 'local_syllabus_timeline'));
echo $OUTPUT->header();

$table = new html_table();
$table->width = '100%';
$table->attributes = array('class'=>'admintable generaltable','style'=>'display: table;');//table-layout:fixed;
$table->head  = array('', get_string('topic','local_syllabus_timeline'), get_string('outline','local_syllabus_timeline')
, get_string('talk','local_syllabus_timeline'), get_string('demo','local_syllabus_timeline')
, get_string('homework','local_syllabus_timeline'), get_string('other','local_syllabus_timeline'), get_string('remark','local_syllabus_timeline'));
$table->align  = array('center', 'left', 'left', 'center', 'center', 'center', 'center', 'left');
$table->size  = array('10%','','','6%','6%','6%','6%');
$numsections = $DB->get_field('course_format_options','value', array('courseid'=>$course->id, 'format'=>$course->format, 'name'=>'numsections'));  // by YCJ
course_create_syllabus_timeline_sections_if_missing($course->id, $numsections);

$sql = "SELECT * FROM {syllabus_timeline} WHERE course = :courseid AND section <= :numsections";
$timelines = $DB->get_records_sql($sql, array('courseid'=>$course->id, 'numsections'=>$numsections));

foreach($timelines as $tl){
    $data = array();
    $data[] = get_string('section', 'local_syllabus_timeline', $tl->section);
    $data[] = $tl->topic;
    $data[] = str_replace(chr(13).chr(10), "<br />",$tl->outline);
    $data[] = $tl->talk;
    $data[] = $tl->demo;
    $data[] = $tl->homework;
    $data[] = $tl->other;
    $data[] = str_replace(chr(13).chr(10), "<br />",$tl->remark);
    
    $table->data[] = new html_table_row($data);
}
if(has_capability('moodle/course:update',$context)){         
    $url = $CFG->wwwroot.'/local/syllabus_timeline/edit.php?id='.$course->id;
    echo html_writer::tag('a', get_string('editall', 'local_syllabus_timeline') , array('href' => $url, 'class' => 'btn btn-info','style' => 'margin: 3px 3px', 'color'=> 'white', 'title' => get_string('editall', 'local_syllabus_timeline')));
}
echo html_writer::table($table);
local_syllabus_timeline_standard_log_view($course);
echo $OUTPUT->footer();