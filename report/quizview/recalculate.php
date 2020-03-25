<?php

require('../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir.'/adminlib.php');
require('locallib.php');

$id = required_param('id',PARAM_INT);       $recalculate = optional_param('recalculate', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_URL);

require_sesskey();
$course = $DB->get_record('course', array('id'=>$id));
require_login($course);
require_capability('report/quizview:view', context_course::instance($course->id));
$PAGE->set_url($returnurl);
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_pagelayout('report');

if($recalculate){
    $track = new report_quizview_track();
    $return = $track->report_quizview_analysis($course->id);

    if($return){
        if(!$DB->record_exists('weekly_log_quiz', array('courseid'=>$course->id))){
            echo $OUTPUT->header();            
            echo '<script type="text/javascript">alert("' . get_string('continue','report_quizview').'")</script>';
            redirect($returnurl);
        }
    }
}

if(!empty($returnurl)){
    redirect($returnurl);
}
