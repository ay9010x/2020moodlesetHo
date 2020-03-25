<?php
/**
 * Version details.
 *
 * @package    local
 * @subpackage syllabus_week
 * @copyright  2017 Click-AP <mary@click-ap.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
$PAGE->set_url('/local/syllabus_week/index.php', array('id' => $course->id));
$PAGE->navbar->add(get_string('pluginname', 'local_syllabus_week'));
$PAGE->set_title(get_string('pluginname', 'local_syllabus_week'));
$PAGE->set_heading(get_string('pluginname', 'local_syllabus_week'));
echo $OUTPUT->header();

$table = new html_table();
$table->width = '100%';
$table->attributes = array('class'=>'admintable generaltable','style'=>'display: table;');//table-layout:fixed;
$table->head  = array('');
$table->size  = array('10%');
$table->align  = array('center');

$other_heads = $DB->get_records('syllabus_week_setting', array('course'=>$course->id));
foreach($other_heads as $head){
    if($head->date){
        $table->head[] = get_string('date', 'local_syllabus_week');
        $table->align[] = 'center';
        $table->size[]  = '10%';
    }
    if($head->week){
        $table->head[] = get_string('week', 'local_syllabus_week');
        $table->align[] = 'center';
        $table->size[]  = '6%';
    }
    if($head->session){
        $table->head[] = get_string('session', 'local_syllabus_week');
        $table->align[] = 'center';
        $table->size[]  = '6%';
    }
    if($head->location){
        $table->head[] = get_string('location', 'local_syllabus_week');
        $table->align[] = 'left';
        $table->size[]  = '10%';
    }
}
$table->head[] = get_string('summary', 'local_syllabus_week');
$table->align[] = 'left';

$numsections = $DB->get_field('course_format_options','value', array('courseid'=>$course->id, 'format'=>$course->format, 'name'=>'numsections'));   // by YCJ
course_create_syllabus_week_sections_if_missing($course->id, $numsections);

$sql = "SELECT * FROM {syllabus_week} WHERE course = :courseid AND section <= :numsections";
$weeks = $DB->get_records_sql($sql, array('courseid'=>$course->id, 'numsections'=>$numsections));

foreach($weeks as $week){
    $data = array();
    $data[] = get_string('sectionname', 'local_syllabus_week', $week->section);
    foreach($other_heads as $head){
        if($head->date){
            $data[] = date('Y/m/d',$week->date);
        }
        if($head->week){
            $data[] = $week->week;
        }
        if($head->session){
            $data[] = $week->session;
        }
        if($head->location){
            $data[] = $week->location;
        }  
    }
    $data[] = $week->summary;
    $table->data[] = new html_table_row($data);
}
if(has_capability('moodle/course:update',$context)){         
    $url = $CFG->wwwroot.'/local/syllabus_week/edit.php?id='.$course->id;
    echo html_writer::tag('a', get_string('editall', 'local_syllabus_week') , array('href' => $url, 'class' => 'btn btn-info','style' => 'margin: 3px 3px', 'color'=> 'white', 'title' => get_string('editall', 'local_syllabus_week')));
    
    $url = $CFG->wwwroot.'/local/syllabus_week/setup.php?id='.$course->id;
    echo html_writer::tag('a', get_string('editsetup', 'local_syllabus_week') , array('href' => $url, 'class' => 'btn btn-info','style' => 'margin: 3px 3px', 'color'=> 'white', 'title' => get_string('editsetup', 'local_syllabus_week')));
}
echo html_writer::table($table);
local_syllabus_week_standard_log_view($course);
echo $OUTPUT->footer();