<?php

function local_mooccourse_get_all_courses_listing($search='', $extraselect='', array $extraparams=null, $page=0, $recordsperpage=0, $sort='startdate', $dir='DESC') {
    global $DB, $CFG;

    $now    = time();
    $select = "c.visible = 1 AND c.id <> :siteid ";
    $params = array();
    $params['siteid'] = SITEID;
    
    if (!empty($search)) {
        $search = trim($search);
        $select .= " AND (". $DB->sql_like('fullname', ':search1', false, false).
                   " OR ". $DB->sql_like('shortname', ':search2', false, false).
                   " )";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
    }
    
    if ($sort) {
        $sort = " ORDER BY $sort $dir";
    }
              
    return $DB->get_records_sql("SELECT c.id, c.shortname, c.fullname, c.summary, c.summaryformat, c.startdate, c.visible,
                                 ca.name as cat_name 
                                 FROM {course} c
                                 JOIN {course_categories} ca ON c.category = ca.id
                                 WHERE $select $extraselect
                                 $sort", array_merge($params, $extraparams), $page, $recordsperpage);
}

function local_mooccourse_update_courseinfo($data, $editoroptions = NULL){
    global $CFG, $DB;

    $data->timemodified = time();
    $oldcourse = course_get_format($data->id)->get_course();
    $context   = context_course::instance($oldcourse->id);
    
    if ($overviewfilesoptions = course_overviewfiles_options($oldcourse->id)) {
        $data = file_postupdate_standard_filemanager($data, 'overviewfiles', $overviewfilesoptions, $context, 'course', 'overviewfiles', 0);
            }
    if(isset($data->summary_editor)){
        $data = file_postupdate_standard_editor($data, 'summary', $editoroptions, $context, 'course', 'summary', 0);
    }
    if(isset($data->outline_editor)){
        $data = file_postupdate_standard_editor($data, 'outline', $editoroptions, $context, 'course', 'outline', 0);
    }
    if(isset($data->point_editor)){
        $data = file_postupdate_standard_editor($data, 'point', $editoroptions, $context, 'course', 'point', 0); 
    }
    if(isset($data->officehour_editor)){
        $data = file_postupdate_standard_editor($data, 'officehour', $editoroptions, $context, 'course', 'officehour', 0); 
    }
    // by YCJ
    if(isset($data->bible_editor)){
        $data = file_postupdate_standard_editor($data, 'bible', $editoroptions, $context, 'course', 'bible', 0); 
    }
    if(isset($data->qna_editor)){
        $data = file_postupdate_standard_editor($data, 'qna', $editoroptions, $context, 'course', 'qna', 0); 
    }
    
    $DB->update_record('course', $data);
    rebuild_course_cache($data->id);
        course_get_format($data->id)->update_course_format_options($data, $oldcourse);
    $course = $DB->get_record('course', array('id'=>$data->id));

                enrol_course_updated(false, $course, $data);
    
        $event = \core\event\course_updated::create(array(
        'objectid' => $course->id,
        'context' => context_course::instance($course->id),
        'other' => array('shortname' => $course->shortname,
                         'fullname' => $course->fullname)
    ));

    $event->set_legacy_logdata(array($course->id, 'course', 'update', 'edit.php?id=' . $course->id, $course->id));
    $event->trigger();
    
    if ($oldcourse->format !== $course->format) {
                                $DB->delete_records('course_format_options',
                array('courseid' => $course->id, 'format' => $oldcourse->format));
    }
}