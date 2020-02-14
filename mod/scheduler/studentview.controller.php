<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/scheduler/mailtemplatelib.php');

$returnurl = new moodle_url('/mod/scheduler/view.php', array('id' => $cm->id));



if ($action == 'bookslot') {

    require_sesskey();
    require_capability('mod/scheduler:appoint', $context);

        $slotid = required_param('slotid', PARAM_INT);
    $slot = $scheduler->get_slot($slotid);
    if (!$slot) {
        throw new moodle_exception('error');
    }

    if (!$slot->is_in_bookable_period()) {
        throw new moodle_exception('nopermissions');
    }

    $requiredcapacity = 1;
    $userstobook = array($USER->id);
    if ($appointgroup) {
        $groupmembers = $scheduler->get_available_students($appointgroup);
        $requiredcapacity = count($groupmembers);
        $userstobook = array_keys($groupmembers);
    }

    $errormessage = '';

    $bookinglimit = $scheduler->count_bookable_appointments($USER->id, false);
    if ($bookinglimit == 0) {
        $errormessage = get_string('selectedtoomany', 'scheduler', $bookinglimit);
    }
    if (!$errormessage) {
                $existingstudents = array();
        foreach ($slot->get_appointments() as $app) {
            $existingstudents[] = $app->studentid;
        }
        $userstobook = array_diff($userstobook, $existingstudents);

        $remaining = $slot->count_remaining_appointments();
                if ($remaining >= 0 && $remaining < $requiredcapacity) {
            if ($requiredcapacity > 1) {
                $errormessage = get_string('notenoughplaces', 'scheduler');
            } else {
                $errormessage = get_string('slot_is_just_in_use', 'scheduler');
            }
        }
    }

    if ($errormessage) {
        echo $output->header();
        echo $output->box($errormessage, 'error');
        echo $output->continue_button($returnurl);
        echo $output->footer();
        exit();
    }

        foreach ($userstobook as $studentid) {
        $appointment = $slot->create_appointment();
        $appointment->studentid = $studentid;
        $appointment->attended = 0;
        $appointment->timecreated = time();
        $appointment->timemodified = time();

        \mod_scheduler\event\booking_added::create_from_slot($slot)->trigger();

                if ($scheduler->allownotifications) {
            $student = $DB->get_record('user', array('id' => $appointment->studentid));
            $teacher = $DB->get_record('user', array('id' => $slot->teacherid));
            scheduler_messenger::send_slot_notification($slot, 'bookingnotification', 'applied',
                                                        $student, $teacher, $teacher, $student, $course);
        }
    }
    $slot->save();
    redirect($returnurl);
}




if ($action == 'cancelbooking') {

    require_sesskey();
    require_capability('mod/scheduler:appoint', $context);

        $slotid = required_param('slotid', PARAM_INT);
    $slot = $scheduler->get_slot($slotid);
    if (!$slot) {
        throw new moodle_exception('error');
    }

    if (!$slot->is_in_bookable_period()) {
        throw new moodle_exception('nopermissions');
    }

    $userstocancel = array($USER->id);
    if ($appointgroup) {
        $userstocancel = array_keys($scheduler->get_available_students($appointgroup));
    }

    foreach ($userstocancel as $userid) {
        if ($appointment = $slot->get_student_appointment($userid)) {
            $scheduler->delete_appointment($appointment->id);

                        if ($scheduler->allownotifications) {
                $student = $DB->get_record('user', array('id' => $USER->id));
                $teacher = $DB->get_record('user', array('id' => $slot->teacherid));
                scheduler_messenger::send_slot_notification($slot, 'bookingnotification', 'cancelled',
                                                            $student, $teacher, $teacher, $student, $COURSE);
            }
            \mod_scheduler\event\booking_removed::create_from_slot($slot)->trigger();
        }
    }
    redirect($returnurl);

}
