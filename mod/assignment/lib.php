<?php




function assignment_add_instance($assignment, $mform = null) {
    global $DB;

    $assignment->timemodified = time();
    $assignment->courseid = $assignment->course;
    $returnid = $DB->insert_record("assignment", $assignment);
    $assignment->id = $returnid;
    return $returnid;
}


function assignment_delete_instance($id){
    global $CFG, $DB;

    if (! $assignment = $DB->get_record('assignment', array('id'=>$id))) {
        return false;
    }

    $result = true;
        $fs = get_file_storage();
    if ($cm = get_coursemodule_from_instance('assignment', $assignment->id)) {
        $context = context_module::instance($cm->id);
        $fs->delete_area_files($context->id);
    }

    if (! $DB->delete_records('assignment_submissions', array('assignment'=>$assignment->id))) {
        $result = false;
    }

    if (! $DB->delete_records('event', array('modulename'=>'assignment', 'instance'=>$assignment->id))) {
        $result = false;
    }

    if (! $DB->delete_records('assignment', array('id'=>$assignment->id))) {
        $result = false;
    }

    grade_update('mod/assignment', $assignment->course, 'mod', 'assignment', $assignment->id, 0, NULL, array('deleted'=>1));

    return $result;
}


function assignment_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}
