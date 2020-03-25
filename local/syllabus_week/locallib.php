<?php

function course_create_syllabus_week_sections_if_missing($courseid, $numsections) {
    global $DB, $USER;
    
    $sql = "SELECT id,section FROM {course_sections} WHERE course = :courseid AND section <= :numsections";
    $sections = $DB->get_records_sql_menu($sql, array('courseid'=>$courseid, 'numsections'=>$numsections));
    
    foreach ($sections as $sectionnum) {
        if (!$DB->record_exists('syllabus_week', array('course'=>$courseid, 'section'=>$sectionnum))) {
            $data = new stdClass();
            $data->course   = $courseid;
            $data->section  = $sectionnum;
            $data->date  = '';
            $data->week = '';
            $data->session = '';
            $data->location = '';
            $data->summary = '';
            $data->userid = $USER->id;
            $data->timecreated = time();
            $id = $DB->insert_record("syllabus_week", $data);
        }
    }
}
function course_create_syllabus_week_setting_if_missing($courseid) {
    global $DB, $USER;
    
    if (!$DB->record_exists('syllabus_week_setting', array('course'=>$courseid))) {
        $data = new stdClass();
        $data->course   = $courseid;
        $data->date  = 0;
        $data->week = 0;
        $data->session = 0;
        $data->location = 0;
        $data->userid = $USER->id;
        $data->timecreated = time();
        $id = $DB->insert_record("syllabus_week_setting", $data);
    }
}

function local_syllabus_week_update($formdata, $courseid){
    global $DB, $USER;
    $numsections = $DB->get_field('course_format_options','value', array('courseid'=>$courseid, 'name'=>'numsections'));
    for($i=0; $i<=$numsections; $i++){
        $datekey = 'date'.$i;
        $weekkey = 'week'.$i;
        $sessionkey = 'session'.$i;
        $locationkey = 'location'.$i;
        $summarykey = 'summary'.$i;
        
        $data           = new stdClass();
        $data->id       = $DB->get_field('syllabus_week', 'id', array('course'=>$courseid, 'section'=>$i));
        $data->date     = $formdata->$datekey;
        $data->week     = $formdata->$weekkey;
        $data->session  = $formdata->$sessionkey;
        $data->location = $formdata->$locationkey;
        $data->summary  = $formdata->$summarykey;
        $data->userid   = $USER->id;
        $data->timecreated = time();
        $DB->update_record('syllabus_week', $data);
    }
}

function local_syllabus_week_setup_update($data){
    global $DB, $USER;
    $id = $DB->get_field('syllabus_week_setting', 'id', array('course'=>$data->id));
    
    $ws = new stdClass();
    $ws->id = $id;
    $ws->date = $data->date;
    $ws->week = $data->week;
    $ws->session = $data->session;
    $ws->location = $data->location;
    $ws->userid = $USER->id;
    $ws->timemodified = time();
    $DB->update_record('syllabus_week_setting', $ws);
}