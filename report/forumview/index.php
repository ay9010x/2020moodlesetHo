<?php
/**
 * Report Material View CLI Cron(Click-AP)
 *
 * @package       forumview
 * @copyright     2016 Click-AP learning system Ltd.
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
    admin_externalpage_setup('report_forumview');
}else{
    $course = $DB->get_record('course', array('id'=>$id));
    require_login($course);
}
require_capability('report/forumview:view', context_course::instance($course->id));

$returnurl = new moodle_url("/report/forumview/index.php",$params);
$PAGE->set_url($returnurl);
$PAGE->set_pagelayout('report');
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_title(get_string('pluginname', 'report_forumview'));
$PAGE->set_heading(get_string('forumview', 'report_forumview'));

echo $OUTPUT->header();
if(file_exists("$CFG->dirroot/blocks/course_menu/report_table.php")){
    $title = new stdClass();
    $title->course = $course->fullname;
    $title->report = get_string('pluginname', 'report_forumview');
    echo html_writer::tag('h2', get_string('weeks_report', 'block_course_menu', $title));
    echo html_writer::tag('h5', get_string('weeks_report_memo', 'block_course_menu'));
    include("$CFG->dirroot/blocks/course_menu/report_table.php");
}
if($id == SITEID){
    $sql = "SELECT c.id,c.fullname FROM {weekly_log_forum} st
            LEFT JOIN {course} c ON st.courseid = c.id
            GROUP BY c.id";
    $option = $DB->get_records_sql_menu($sql);
    
    $table = new html_table();  
    $table->id = 'searchcourse';
    $table->attributes = array('class'=>'admintable generaltable','style'=>'white-space: nowrap; display: table;');
    $table->align =  array('right','left','center');
    $table->data[] = array( get_string('course_filter', 'report_forumview'),
                            html_writer::select($option, 'courseid', $course_id, false),  
                            '<input id="submit" name ="submit" type="submit" value="'.get_string('search').'" />') ;
    $actionurl = new moodle_url($CFG->wwwroot.'/report/forumview/index.php');
    echo html_writer::start_tag('form', array('id' => 'searchcourse', 'action' => $actionurl, 'method' => 'post'));
    echo html_writer::start_tag('div');
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::table($table);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('form');
}
$finallydate = $DB->get_field_sql("SELECT max(timecreated) FROM {weekly_log_forum} WHERE courseid = :courseid", array('courseid'=>$course->id));
$total_view = $total_user = $total_discussion = $total_discussionuser = $total_postview = $total_postuser = $discussion_cnt = 0;

echo html_writer::start_tag('div');
$table_info = new html_table();  
$table_info->id = 'course info';
$table_info->attributes = array('class'=>'admintable generaltable','style'=>'white-space: nowrap; display: table;');
$table_info->align =  array('center','center','center','center');
    
$table_info->head = array(get_string('fullname', 'report_forumview'),
                       get_string('startdate', 'report_landview'),
                       get_string('enddate', 'report_forumview'),
                       get_string('lastanalysisdate', 'report_forumview'));
$fullname = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'" target="_blank">'.$course->fullname.'</a>';
if($finallydate){
    $finallydate = date('Y/m/d H:i', $finallydate);
}else{
    $finallydate = '';
}
$table_info->data[] = array($fullname, date('Y/m/d H:i', $course->startdate), date('Y/m/d H:i', $course->enddate), $finallydate);
echo html_writer::table($table_info);    
echo html_writer::end_tag('div');

if($course->id != SITEID && !$DB->record_exists('weekly_log_forum', array('courseid'=>$course->id, 'startdate'=>$course->startdate, 'enddate'=>$course->enddate))){
    //recalculate
    $actionurl = new moodle_url('/report/forumview/recalculate.php', array('sesskey'=>sesskey(), 'recalculate'=>1));
    $actionurl->param('id', $course->id);
    $actionurl->param('returnurl', $returnurl);

    echo $OUTPUT->box_start('generalbox', 'notice');
    echo html_writer::tag('p', get_string('datemodify', 'report_forumview', $course));
    echo $OUTPUT->box($OUTPUT->single_button($actionurl, get_string('recalculate', 'report_forumview'), 'post'), 'clearfix mdl-align');
    echo $OUTPUT->box_end();
}


echo html_writer::start_tag('div');
$table_weekly        = new html_table();  
$table_weekly->id    = 'weekly-report';
$table_weekly->attributes = array('class'=>'admintable generaltable','style'=>'white-space: nowrap; display: table;');
$table_weekly->align =  array('center','left','right','center','right','right','right','right','right');
$table_weekly->head  = array(get_string('sectionname', 'report_forumview'),
                       get_string('name'),
                       get_string('discussion', 'report_forumview'),
                       get_string('period', 'report_forumview'),
                       get_string('viewcount', 'report_forumview'),
                       get_string('viewuser', 'report_forumview'),
                       get_string('discussionuser', 'report_forumview'),
                       get_string('postview', 'report_forumview'),
                       get_string('postuser', 'report_forumview'));

$sql = "SELECT DISTINCT(cmid) as cmid, module, instance, sectionid FROM {weekly_log_forum} WHERE courseid = :courseid ";
$modules = $DB->get_records_sql($sql, array('courseid'=>$course->id));
$cnt = 1;

foreach($modules as $mod){
    $tracks = $DB->get_records('weekly_log_forum', array('courseid'=>$course->id, 'cmid'=>$mod->cmid));
    
    //$sectionname = $DB->get_field('course_sections', 'name', array('id'=>$mod->sectionid));
    $section = $DB->get_record('course_sections', array('id'=>$mod->sectionid));
    $modulename = $DB->get_field($mod->module, 'name', array('id'=>$mod->instance));
    
    //如果section 名稱為null，則相辦法把預設的名單帶出來
    $cells = array();
    $cells[0] = new html_table_cell();
    $cells[0]->style = 'text-align: center;';
    $cells[0]->rowspan = sizeof($tracks);
    $cells[0]->text = get_section_name($course, $section);
    
    $cells[1] = new html_table_cell();
    $cells[1]->style = 'text-align: left;';
    $cells[1]->rowspan = sizeof($tracks);
    $cells[1]->text = $modulename;

    /*$data[0] = new html_table_cell($cnt);
    $data[1] = new html_table_cell($section->name);
    $data[0]->rowspan = sizeof($tracks);
    $data[1]->rowspan = sizeof($tracks);
    */
    $i = 0;
    foreach($tracks as $log){
        $total_view = $total_view + $log->view;
        $total_user = $total_user + $log->viewuser;
        $discussion_cnt = $log->discussion;
        $total_discussionuser = $total_discussionuser + $log->discussionuser;
        $total_postview = $total_postview + $log->postview;
        $total_postuser = $total_postuser + $log->postuser;
        
        $period =  date('Y/m/d H:i', $log->cyclebegin).' ~ '. date('Y/m/d H:i', $log->cycleend);
            
        $cells[2] = new html_table_cell();
        $cells[2]->style = 'text-align: right;';
        $cells[2]->text = $log->discussion;
        
        $cells[3] = new html_table_cell();
        $cells[3]->style = 'text-align: center;';
        $cells[3]->text = $period;
        
        $cells[4] = new html_table_cell();
        $cells[4]->style = 'text-align: right;';
        $cells[4]->text = $log->view;
        
        $cells[5] = new html_table_cell();
        $cells[5]->style = 'text-align: right;';
        $cells[5]->text = $log->viewuser;
        
        $cells[6] = new html_table_cell();
        $cells[6]->style = 'text-align: right;';
        $cells[6]->text = $log->discussionuser;
        
        $cells[7] = new html_table_cell();
        $cells[7]->style = 'text-align: right;';
        $cells[7]->text = $log->postview;
        
        $cells[8] = new html_table_cell();
        $cells[8]->style = 'text-align: right;';
        $cells[8]->text = $log->postuser;
        $row = new html_table_row($cells);
        
        $table_weekly->data[] = $row;
        
        unset($cells[0]);
        unset($cells[1]);
    }
    $total_discussion = $total_discussion + $discussion_cnt;
    $cnt++;
}
$table_weekly->data[] = array('', get_string('total', 'report_forumview'), $total_discussion, '', $total_view, $total_user, $total_discussionuser, $total_postview, $total_postuser);

echo html_writer::table($table_weekly);    
echo html_writer::end_tag('div');

echo $OUTPUT->footer();