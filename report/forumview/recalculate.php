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
require_capability('report/forumview:view', context_course::instance($course->id));

if($recalculate){
    $track = new report_forumview_track();
    $track->report_forumview_analysis($course->id);
}

if(!empty($returnurl)){
    redirect($returnurl);
}