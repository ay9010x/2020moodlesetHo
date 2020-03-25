<?php

function course_create_syllabus_timeline_sections_if_missing($courseid, $numsections) {
    global $DB, $USER;
    
    $sql = "SELECT id,section FROM {course_sections} WHERE course = :courseid AND section <= :numsections";
    $sections = $DB->get_records_sql_menu($sql, array('courseid'=>$courseid, 'numsections'=>$numsections));
    
    foreach ($sections as $sectionnum) {
        if (!$DB->record_exists('syllabus_timeline', array('course'=>$courseid, 'section'=>$sectionnum))) {
            $data = new stdClass();
            $data->course   = $courseid;
            $data->section  = $sectionnum;
            $data->topic  = '';
            $data->outline = '';
            $data->talk = '';
            $data->demo = '';
            $data->homework = '';
            $data->other = '';
            $data->remark = '';
            $data->userid = $USER->id;
            $data->timecreated = time();
            $id = $DB->insert_record("syllabus_timeline", $data);
        }
    }
}

function local_syllabus_timeline_update($formdata, $courseid){
    global $DB, $USER;
    $numsections = $DB->get_field('course_format_options','value', array('courseid'=>$courseid, 'name'=>'numsections'));
    for($i=0; $i<=$numsections; $i++){
        $topickey    = 'topic'.$i;
        $outlinekey  = 'outline'.$i;
        $talkkey     = 'talk'.$i;
        $demokey     = 'demo'.$i;
        $homeworkkey = 'homework'.$i;
        $otherkey    = 'other'.$i;
        $remarkkey   = 'remark'.$i;
        
        $data           = new stdClass();
        $data->id       = $DB->get_field('syllabus_timeline', 'id', array('course'=>$courseid, 'section'=>$i));
        $data->topic    = $formdata->$topickey;
        $data->outline  = $formdata->$outlinekey;
        $data->talk     = $formdata->$talkkey;
        $data->demo     = $formdata->$demokey;
        $data->homework = $formdata->$homeworkkey;
        $data->other    = $formdata->$otherkey;
        $data->remark   = $formdata->$remarkkey;
        $data->userid   = $USER->id;
        $data->timecreated = time();
        $DB->update_record('syllabus_timeline', $data);
    }
}