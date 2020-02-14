<?PHP



defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/mod/scheduler/locallib.php');
require_once($CFG->dirroot.'/mod/scheduler/mailtemplatelib.php');
require_once($CFG->dirroot.'/mod/scheduler/renderer.php');
require_once($CFG->dirroot.'/mod/scheduler/renderable.php');

define('SCHEDULER_TIMEUNKNOWN', 0);  define('SCHEDULER_SELF', 0); define('SCHEDULER_OTHERS', 1); define('SCHEDULER_ALL', 2); 
define ('SCHEDULER_MEAN_GRADE', 0); define ('SCHEDULER_MAX_GRADE', 1);  

function scheduler_add_instance($scheduler) {
    global $DB;

    $scheduler->timemodified = time();
    $scheduler->scale = isset($scheduler->grade) ? $scheduler->grade : 0;

    $id = $DB->insert_record('scheduler', $scheduler);
    $scheduler->id = $id;

    scheduler_grade_item_update($scheduler);

    return $id;
}


function scheduler_update_instance($scheduler) {
    global $DB;

    $scheduler->timemodified = time();
    $scheduler->id = $scheduler->instance;

    $scheduler->scale = $scheduler->grade;

    $DB->update_record('scheduler', $scheduler);

        scheduler_update_grades($scheduler);

    return true;
}



function scheduler_delete_instance($id) {
    global $DB;

    if (! $DB->record_exists('scheduler', array('id' => $id))) {
        return false;
    }

    $scheduler = scheduler_instance::load_by_id($id);
    $scheduler->delete();

        $params = array('modulename' => 'scheduler', 'instance' => $id);
    $DB->delete_records('event', $params);

    return true;
}


function scheduler_user_outline($course, $user, $mod, $scheduler) {

    $scheduler = scheduler_instance::load_by_coursemodule_id($mod->id);
    $upcoming = count($scheduler->get_upcoming_slots_for_student($user->id));
    $attended = count($scheduler->get_attended_slots_for_student($user->id));

    $text = '';

    if ($attended + $upcoming > 0) {
        $a = array('attended' => $attended, 'upcoming' => $upcoming);
        $text .= get_string('outlineappointments', 'scheduler', $a);
    }

    if ($scheduler->uses_grades()) {
        $grade = $scheduler->get_gradebook_info($user->id);
        if ($grade) {
            $text .= get_string('outlinegrade', 'scheduler', $grade->str_long_grade);
        }
    }

    $return = new stdClass();
    $return->info = $text;
    return $return;
}


function scheduler_user_complete($course, $user, $mod, $scheduler) {

    global $PAGE;

    $scheduler = scheduler_instance::load_by_coursemodule_id($mod->id);
    $output = $PAGE->get_renderer('mod_scheduler', null, RENDERER_TARGET_GENERAL);

    $appointments = $scheduler->get_appointments_for_student($user->id);

    if (count($appointments) > 0) {
        $table = new scheduler_slot_table($scheduler);
        $table->showattended = true;
        foreach ($appointments as $app) {
            $table->add_slot($app->get_slot(), $app, null, false);
        }

        echo $output->render($table);
    } else {
        echo get_string('noappointments', 'scheduler');
    }

    if ($scheduler->uses_grades()) {
        $grade = $scheduler->get_gradebook_info($user->id);
        if ($grade) {
            $info = new scheduler_totalgrade_info($scheduler, $grade);
            echo $output->render($info);
        }
    }

}


function scheduler_print_recent_activity($course, $isteacher, $timestart) {

    return false;
}



function scheduler_scale_used($cmid, $scaleid) {
    global $DB;

    $return = false;

        $rec = $DB->get_record('scheduler', array('id' => $cmid, 'scale' => -$scaleid));

    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }

    return $return;
}



function scheduler_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('scheduler', array('scale' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}





function scheduler_reset_course_form_definition(&$mform) {
    global $COURSE, $DB;

    $mform->addElement('header', 'schedulerheader', get_string('modulenameplural', 'scheduler'));

    if ($DB->record_exists('scheduler', array('course' => $COURSE->id))) {

        $mform->addElement('checkbox', 'reset_scheduler_slots', get_string('resetslots', 'scheduler'));
        $mform->addElement('checkbox', 'reset_scheduler_appointments', get_string('resetappointments', 'scheduler'));
        $mform->disabledIf('reset_scheduler_appointments', 'reset_scheduler_slots', 'checked');
    }
}


function scheduler_reset_course_form_defaults($course) {
    return array('reset_scheduler_slots' => 1, 'reset_scheduler_appointments' => 1);
}



function scheduler_reset_userdata($data) {
    global $CFG, $DB;

    $status = array();
    $componentstr = get_string('modulenameplural', 'scheduler');

    $sqlfromslots = 'FROM {scheduler_slots} WHERE schedulerid IN '.
        '(SELECT sc.id FROM {scheduler} sc '.
        ' WHERE sc.course = :course)';

    $params = array('course' => $data->courseid);

    $strreset = get_string('reset');

    if (!empty($data->reset_scheduler_appointments) || !empty($data->reset_scheduler_slots)) {

        $slots = $DB->get_recordset_sql('SELECT * '.$sqlfromslots, $params);
        $success = true;
        foreach ($slots as $slot) {
                        $success = $success && scheduler_delete_calendar_events($slot);

                        $success = $success && $DB->delete_records('scheduler_appointment', array('slotid' => $slot->id));
        }
        $slots->close();

                $schedulers = $DB->get_records('scheduler', $params);
        foreach ($schedulers as $scheduler) {
            scheduler_grade_item_update($scheduler, 'reset');
        }

        $status[] = array(
                        'component' => $componentstr,
                        'item' => get_string('resetappointments', 'scheduler'),
                        'error' => !$success
                    );
    }
    if (!empty($data->reset_scheduler_slots)) {
        if ($DB->execute('DELETE '.$sqlfromslots, $params)) {
            $status[] = array('component' => $componentstr, 'item' => get_string('resetslots', 'scheduler'), 'error' => false);
        }
    }
    return $status;
}


function scheduler_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}





function scheduler_update_grades($schedulerrecord, $userid=0, $nullifnone=true) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    $scheduler = scheduler_instance::load_by_id($schedulerrecord->id);

    if ($scheduler->scale == 0) {
        scheduler_grade_item_update($schedulerrecord);

    } else if ($grades = $scheduler->get_user_grades($userid)) {
        foreach ($grades as $k => $v) {
            if ($v->rawgrade == -1) {
                $grades[$k]->rawgrade = null;
            }
        }
        scheduler_grade_item_update($schedulerrecord, $grades);

    } else {
        scheduler_grade_item_update($schedulerrecord);
    }
}



function scheduler_grade_item_update($scheduler, $grades=null) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    if (!isset($scheduler->courseid)) {
        $scheduler->courseid = $scheduler->course;
    }
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'scheduler'));
    $cmid = $DB->get_field('course_modules', 'id', array('module' => $moduleid, 'instance' => $scheduler->id));

    if ($scheduler->scale == 0) {
                scheduler_grade_item_delete($scheduler);
        return 0;
    } else {
        $params = array('itemname' => $scheduler->name, 'idnumber' => $cmid);

        if ($scheduler->scale > 0) {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = $scheduler->scale;
            $params['grademin']  = 0;

        } else if ($scheduler->scale < 0) {
            $params['gradetype'] = GRADE_TYPE_SCALE;
            $params['scaleid']   = -$scheduler->scale;

        } else {
            $params['gradetype'] = GRADE_TYPE_TEXT;         }

        if ($grades === 'reset') {
            $params['reset'] = true;
            $grades = null;
        }

        return grade_update('mod/scheduler', $scheduler->courseid, 'mod', 'scheduler', $scheduler->id, 0, $grades, $params);
    }
}




function scheduler_upgrade_grades() {
    global $DB;

    $sql = "SELECT COUNT('x')
        FROM {scheduler} s, {course_modules} cm, {modules} m
        WHERE m.name='scheduler' AND m.id=cm.module AND cm.instance=s.id";
    $count = $DB->count_records_sql($sql);

    $sql = "SELECT s.*, cm.idnumber AS cmidnumber, s.course AS courseid
        FROM {scheduler} s, {course_modules} cm, {modules} m
        WHERE m.name='scheduler' AND m.id=cm.module AND cm.instance=s.id";
    $rs = $DB->get_recordset_sql($sql);
    if ($rs->valid()) {
        $pbar = new progress_bar('schedulerupgradegrades', 500, true);
        $i = 0;
        foreach ($rs as $scheduler) {
            $i++;
            upgrade_set_timeout(60 * 5);             scheduler_update_grades($scheduler);
            $pbar->update($i, $count, "Updating scheduler grades ($i/$count).");
        }
        upgrade_set_timeout();     }
    $rs->close();
}



function scheduler_grade_item_delete($scheduler) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if (!isset($scheduler->courseid)) {
        $scheduler->courseid = $scheduler->course;
    }

    return grade_update('mod/scheduler', $scheduler->courseid, 'mod', 'scheduler', $scheduler->id, 0, null, array('deleted' => 1));
}





function scheduler_get_file_areas($course, $cm, $context) {
    return array(
            'slotnote' => get_string('areaslotnote', 'scheduler'),
            'appointmentnote' => get_string('areaappointmentnote', 'scheduler'),
            'teachernote' => get_string('areateachernote', 'scheduler')
    );
}


function scheduler_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB, $USER;

    
    if (!has_any_capability(array('mod/scheduler:appoint', 'mod/scheduler:attend'), $context)) {
        return null;
    }

    require_once(dirname(__FILE__).'/locallib.php');

    $validareas = array_keys(scheduler_get_file_areas($course, $cm, $context));
    if (!in_array($filearea, $validareas)) {
        return null;
    }

    if (is_null($itemid)) {
        return new scheduler_file_info($browser, $course, $cm, $context, $areas, $filearea);
    }

    try {
        $scheduler = scheduler_instance::load_by_coursemodule_id($cm->id);

        if ($filearea === 'slotnote') {
            $slot = $scheduler->get_slot($itemid);

            $cansee = true;
            $canwrite = $USER->id == $slot->teacherid
                        || has_capability('mod/scheduler:manageallappointments', $context);
            $name = get_string('slot', 'scheduler'). ' '.$itemid;

        } else if ($filearea === 'appointmentnote') {
            if (!$scheduler->uses_appointmentnotes()) {
                return null;
            }
            list($slot, $app) = $scheduler->get_slot_appointment($itemid);
            $cansee = $USER->id == $app->studentid || $USER->id == $slot->teacherid
                        || has_capability('mod/scheduler:manageallappointments', $context);
            $canwrite = $USER->id == $slot->teacherid
                        || has_capability('mod/scheduler:manageallappointments', $context);
            $name = get_string('appointment', 'scheduler'). ' '.$itemid;

        } else if ($filearea === 'teachernote') {
            if (!$scheduler->uses_teachernotes()) {
                return null;
            }

            list($slot, $app) = $scheduler->get_slot_appointment($itemid);
            $cansee = $USER->id == $slot->teacherid
                        || has_capability('mod/scheduler:manageallappointments', $context);
            $canwrite = $cansee;
            $name = get_string('appointment', 'scheduler'). ' '.$itemid;
        }

        $fs = get_file_storage();
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;
        if (!$storedfile = $fs->get_file($context->id, 'mod_scheduler', $filearea, $itemid, $filepath, $filename)) {
            return null;
        }

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $name, true, true, $canwrite, false);
    } catch (Exception $e) {
        return null;
    }
}


function scheduler_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_any_capability(array('mod/scheduler:appoint', 'mod/scheduler:attend'), $context)) {
        return false;
    }

    try {
        $scheduler = scheduler_instance::load_by_coursemodule_id($cm->id);

        $entryid = (int)array_shift($args);
        $relativepath = implode('/', $args);

        if ($filearea === 'slotnote') {
            if (!$scheduler->get_slot($entryid)) {
                return false;
            }
            
        } else if ($filearea === 'appointmentnote') {
            if (!$scheduler->uses_appointmentnotes()) {
                return false;
            }

            list($slot, $app) = $scheduler->get_slot_appointment($entryid);
            if (!$app) {
                return false;
            }

            if (!($USER->id == $app->studentid || $USER->id == $slot->teacherid)) {
                require_capability('mod/scheduler:manageallappointments', $context);
            }

        } else if ($filearea === 'teachernote') {
            if (!$scheduler->uses_teachernotes()) {
                return false;
            }

            list($slot, $app) = $scheduler->get_slot_appointment($entryid);
            if (!$app) {
                return false;
            }

            if (!($USER->id == $slot->teacherid)) {
                require_capability('mod/scheduler:manageallappointments', $context);
            }

        } else {
                        return false;
        }
    } catch (Exception $e) {
                return false;
    }

    $fullpath = "/$context->id/mod_scheduler/$filearea/$entryid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

