<?php




function attendance_supports($feature) {
    switch($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
                case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        default:
            return null;
    }
}

function att_add_default_statuses($attid) {
    global $DB;

    $statuses = $DB->get_recordset('attendance_statuses', array('attendanceid' => 0), 'id');
    foreach ($statuses as $st) {
        $rec = $st;
        $rec->attendanceid = $attid;
        $DB->insert_record('attendance_statuses', $rec);
    }
    $statuses->close();
}

function attendance_add_instance($attendance) {
    global $DB;

    $attendance->timemodified = time();

    $attendance->id = $DB->insert_record('attendance', $attendance);

    att_add_default_statuses($attendance->id);

    attendance_grade_item_update($attendance);

    return $attendance->id;
}


function attendance_update_instance($attendance) {
    global $DB;

    $attendance->timemodified = time();
    $attendance->id = $attendance->instance;

    if (! $DB->update_record('attendance', $attendance)) {
        return false;
    }

    attendance_grade_item_update($attendance);

    return true;
}


function attendance_delete_instance($id) {
    global $DB;

    if (! $attendance = $DB->get_record('attendance', array('id' => $id))) {
        return false;
    }

    if ($sessids = array_keys($DB->get_records('attendance_sessions', array('attendanceid' => $id), '', 'id'))) {
        $DB->delete_records_list('attendance_log', 'sessionid', $sessids);
        $DB->delete_records('attendance_sessions', array('attendanceid' => $id));
    }
    $DB->delete_records('attendance_statuses', array('attendanceid' => $id));

    $DB->delete_records('attendance', array('id' => $id));

    attendance_grade_item_delete($attendance);

    return true;
}

function attendance_delete_course($course, $feedback=true) {
    global $DB;

    $attids = array_keys($DB->get_records('attendance', array('course' => $course->id), '', 'id'));
    $sessids = array_keys($DB->get_records_list('attendance_sessions', 'attendanceid', $attids, '', 'id'));
    if ($sessids) {
        $DB->delete_records_list('attendance_log', 'sessionid', $sessids);
    }
    if ($attids) {
        $DB->delete_records_list('attendance_statuses', 'attendanceid', $attids);
        $DB->delete_records_list('attendance_sessions', 'attendanceid', $attids);
    }
    $DB->delete_records('attendance', array('course' => $course->id));

    return true;
}


function attendance_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'attendanceheader', get_string('modulename', 'attendance'));

    $mform->addElement('static', 'description', get_string('description', 'attendance'),
                                get_string('resetdescription', 'attendance'));
    $mform->addElement('checkbox', 'reset_attendance_log', get_string('deletelogs', 'attendance'));

    $mform->addElement('checkbox', 'reset_attendance_sessions', get_string('deletesessions', 'attendance'));
    $mform->disabledIf('reset_attendance_sessions', 'reset_attendance_log', 'notchecked');

    $mform->addElement('checkbox', 'reset_attendance_statuses', get_string('resetstatuses', 'attendance'));
    $mform->setAdvanced('reset_attendance_statuses');
    $mform->disabledIf('reset_attendance_statuses', 'reset_attendance_log', 'notchecked');
}


function attendance_reset_course_form_defaults($course) {
    return array('reset_attendance_log' => 0, 'reset_attendance_statuses' => 0, 'reset_attendance_sessions' => 0);
}

function attendance_reset_userdata($data) {
    global $DB;

    $status = array();

    $attids = array_keys($DB->get_records('attendance', array('course' => $data->courseid), '', 'id'));

    if (!empty($data->reset_attendance_log)) {
        $sess = $DB->get_records_list('attendance_sessions', 'attendanceid', $attids, '', 'id');
        if (!empty($sess)) {
            list($sql, $params) = $DB->get_in_or_equal(array_keys($sess));
            $DB->delete_records_select('attendance_log', "sessionid $sql", $params);
            list($sql, $params) = $DB->get_in_or_equal($attids);
            $DB->set_field_select('attendance_sessions', 'lasttaken', 0, "attendanceid $sql", $params);

            $status[] = array(
                'component' => get_string('modulenameplural', 'attendance'),
                'item' => get_string('attendancedata', 'attendance'),
                'error' => false
            );
        }
    }

    if (!empty($data->reset_attendance_statuses)) {
        $DB->delete_records_list('attendance_statuses', 'attendanceid', $attids);
        foreach ($attids as $attid) {
            att_add_default_statuses($attid);
        }

        $status[] = array(
            'component' => get_string('modulenameplural', 'attendance'),
            'item' => get_string('sessions', 'attendance'),
            'error' => false
        );
    }

    if (!empty($data->reset_attendance_sessions)) {
        $DB->delete_records_list('attendance_sessions', 'attendanceid', $attids);

        $status[] = array(
            'component' => get_string('modulenameplural', 'attendance'),
            'item' => get_string('statuses', 'attendance'),
            'error' => false
        );
    }

    return $status;
}

function attendance_user_outline($course, $user, $mod, $attendance) {
    global $CFG;
    require_once(dirname(__FILE__).'/locallib.php');
    require_once($CFG->libdir.'/gradelib.php');

    $grades = grade_get_grades($course->id, 'mod', 'attendance', $attendance->id, $user->id);

    $result = new stdClass();
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        $result->time = $grade->dategraded;
    } else {
        $result->time = 0;
    }
    if (has_capability('mod/attendance:canbelisted', $mod->context, $user->id)) {
        $statuses = attendance_get_statuses($attendance->id);
        $grade = attendance_get_user_grade(attendance_get_user_statuses_stat($attendance->id, $course->startdate,
                                                                      $user->id, $mod), $statuses);
        $maxgrade = attendance_get_user_max_grade(attendance_get_user_taken_sessions_count($attendance->id, $course->startdate,
                                                                                    $user->id, $mod), $statuses);

        $result->info = $grade.' / '.$maxgrade;
    }

    return $result;
}

function attendance_user_complete($course, $user, $mod, $attendance) {
    global $CFG;

    require_once(dirname(__FILE__).'/renderhelpers.php');
    require_once($CFG->libdir.'/gradelib.php');

    if (has_capability('mod/attendance:canbelisted', $mod->context, $user->id)) {
        echo construct_full_user_stat_html_table($attendance, $user);
    }
}

function attendance_update_grades($attendance, $userid=0, $nullifnone=true) {
    }

function attendance_grade_item_update($attendance, $grades=null) {
    global $CFG, $DB;

    require_once('locallib.php');

    if (!function_exists('grade_update')) {         require_once($CFG->libdir.'/gradelib.php');
    }

    if (!isset($attendance->courseid)) {
        $attendance->courseid = $attendance->course;
    }
    if (!$DB->get_record('course', array('id' => $attendance->course))) {
        error("Course is misconfigured");
    }

    if (!empty($attendance->cmidnumber)) {
        $params = array('itemname' => $attendance->name, 'idnumber' => $attendance->cmidnumber);
    } else {
                $params = array('itemname' => $attendance->name);
    }

    if ($attendance->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $attendance->grade;
        $params['grademin']  = 0;
    } else if ($attendance->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$attendance->grade;

    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/attendance', $attendance->courseid, 'mod', 'attendance', $attendance->id, 0, $grades, $params);
}


function attendance_grade_item_delete($attendance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if (!isset($attendance->courseid)) {
        $attendance->courseid = $attendance->course;
    }

    return grade_update('mod/attendance', $attendance->courseid, 'mod', 'attendance',
                        $attendance->id, 0, null, array('deleted' => 1));
}


function attendance_scale_used ($attendanceid, $scaleid) {
    return false;
}


function attendance_scale_used_anywhere($scaleid) {
    return false;
}


function attendance_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);

    if (!$DB->record_exists('attendance', array('id' => $cm->instance))) {
        return false;
    }

        $fileareas = array('session');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $sessid = (int)array_shift($args);
    if (!$DB->record_exists('attendance_sessions', array('id' => $sessid))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_attendance/$filearea/$sessid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, true);
}
