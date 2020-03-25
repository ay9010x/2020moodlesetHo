<?php
/**
 * Report Land View CLI Cron(Click-AP)
 *
 * @package       LandView
 * @copyright     2017 Click-AP learning system Ltd.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @created by    Click-AP learning system Ltd.
 * @website       www.click-ap.com
 */
require('../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir.'/adminlib.php');
require('locallib.php');

$id = optional_param('id', SITEID, PARAM_INT);
$course_id = optional_param('courseid', 0, PARAM_INT);

$params = array('id' => $id);
if($course_id != 0){
    $course = $DB->get_record('course', array('id'=>$course_id));
    $params['courseid'] = $course_id;
    require_login();
    admin_externalpage_setup('report_landview');
}else{
    $course = $DB->get_record('course', array('id'=>$id));
    require_login($course);
}

require_capability('report/landview:view', context_course::instance($course->id));

$returnurl = new moodle_url("/report/landview/index.php",$params);
$PAGE->set_url($returnurl);
$PAGE->set_pagelayout('report');
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_title(get_string('pluginname', 'report_landview'));
$PAGE->set_heading(get_string('courseview', 'report_landview'));

echo $OUTPUT->header();
if(file_exists("$CFG->dirroot/blocks/course_menu/report_table.php")){
    $title = new stdClass();
    $title->course = $course->fullname;
    $title->report = get_string('pluginname', 'report_landview');
    echo html_writer::tag('h2', get_string('weeks_report', 'block_course_menu', $title));
    echo html_writer::tag('h5', get_string('weeks_report_memo', 'block_course_menu'));
    include("$CFG->dirroot/blocks/course_menu/report_table.php");
}
if($id == SITEID){
    $sql = "SELECT c.id,c.fullname FROM {weekly_log_land} st
            LEFT JOIN {course} c ON st.courseid = c.id
            GROUP BY c.id";
    $option = $DB->get_records_sql_menu($sql);
    
    $table = new html_table();  
    $table->id = 'searchcourse';
    $table->attributes = array('class'=>'admintable generaltable','style'=>'white-space: nowrap; display: table;');
    $table->align =  array('right','left','center');
    $table->data[] = array( get_string('course_filter', 'report_landview'),
                            html_writer::select($option, 'courseid', $course_id, false),  
                            '<input id="submit" name ="submit" type="submit" value="'.get_string('search').'" />') ;
    $actionurl = new moodle_url($CFG->wwwroot.'/report/landview/index.php');
    echo html_writer::start_tag('form', array('id' => 'searchcourse', 'action' => $actionurl, 'method' => 'post'));
    echo html_writer::start_tag('div');
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('form');
}
$finallydate = $DB->get_field_sql("SELECT max(timecreated) FROM {weekly_log_land} WHERE courseid = :courseid", array('courseid'=>$course->id));
$tracks = $DB->get_records('weekly_log_land', array('courseid'=>$course->id));
$total_view = $total_user = 0;

echo html_writer::start_tag('div');
$table_info = new html_table();  
$table_info->id = 'course info';
$table_info->attributes = array('class'=>'admintable generaltable','style'=>'white-space: nowrap; display: table;');
$table_info->align =  array('center','center','center','center');
    
$table_info->head = array(get_string('fullname', 'report_landview'),
                       get_string('startdate', 'report_landview'),
                       get_string('enddate', 'report_landview'),
                       get_string('lastanalysisdate', 'report_landview'));
$fullname = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'" target="_blank">'.$course->fullname.'</a>';
if($finallydate){
    $finallydate = date('Y/m/d H:i', $finallydate);
}else{
    $finallydate = '';
}
$table_info->data[] = array($fullname, date('Y/m/d H:i', $course->startdate), date('Y/m/d H:i', $course->enddate), $finallydate);
echo html_writer::table($table_info);    
echo html_writer::end_tag('div');

if($course->id != SITEID && !$DB->record_exists('weekly_log_land', array('courseid'=>$course->id, 'startdate'=>$course->startdate, 'enddate'=>$course->enddate))){
    //recalculate
    $actionurl = new moodle_url('/report/landview/recalculate.php', array('sesskey'=>sesskey(), 'recalculate'=>1));
    $actionurl->param('id', $course->id);
    $actionurl->param('returnurl', $returnurl);

    echo $OUTPUT->box_start('generalbox', 'notice');
    echo html_writer::tag('p', get_string('datemodify', 'report_landview', $course));
    echo $OUTPUT->box($OUTPUT->single_button($actionurl, get_string('recalculate', 'report_landview'), 'post'), 'clearfix mdl-align');
    echo $OUTPUT->box_end();
}

echo html_writer::start_tag('div');
$table_weekly = new html_table();  
$table_weekly->id = 'weekly view';
$table_weekly->attributes = array('class'=>'admintable generaltable','style'=>'white-space: nowrap; display: table;');
$table_weekly->align =  array('center','center','right','right');
    
$table_weekly->head = array('',get_string('period', 'report_landview'),
                       get_string('viewcount', 'report_landview'),
                       get_string('viewuser', 'report_landview'));

$cnt = 1;
foreach($tracks as $data){
    $total_view = $total_view + $data->view;
    $total_user = $total_user + $data->viewuser;
    $period = date('Y/m/d', $data->cyclebegin).' ~ '.date('Y/m/d', $data->cycleend);
    $table_weekly->data[] = array($cnt++, $period, $data->view, $data->viewuser);
}
$table_weekly->data[] = array('', get_string('total', 'report_landview'), $total_view, $total_user);
echo html_writer::table($table_weekly);    
echo html_writer::end_tag('div');
echo $OUTPUT->footer();