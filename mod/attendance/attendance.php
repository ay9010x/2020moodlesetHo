<?php



require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/student_attendance_form.php');

$pageparams = new mod_attendance_sessions_page_params();

$id = required_param('sessid', PARAM_INT);

$attforsession = $DB->get_record('attendance_sessions', array('id' => $id), '*', MUST_EXIST);
$attendance = $DB->get_record('attendance', array('id' => $attforsession->attendanceid), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('attendance', $attendance->id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);

$pageparams->sessionid = $id;
$att = new mod_attendance_structure($attendance, $cm, $course, $PAGE->context, $pageparams);

require_sesskey();

$mform = new mod_attendance_student_attendance_form(null,
        array('course' => $course, 'cm' => $cm, 'modcontext' => $PAGE->context, 'session' => $attforsession, 'attendance' => $att));

$PAGE->set_url($att->url_sessions());

if ($mform->is_cancelled()) {
        $url = new moodle_url('/mod/attendance/view.php', array('id' => $cm->id));
    redirect($url);
} else if ($fromform = $mform->get_data()) {
    if (!empty($fromform->status)) {
        $success = $att->take_from_student($fromform);

        $url = new moodle_url('/mod/attendance/view.php', array('id' => $cm->id));
        if ($success) {
                        redirect($url);
        } else {
            print_error ('attendance_already_submitted', 'mod_attendance', $url);
        }
    }

        $mform->set_data($fromform);
}

$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->navbar->add($att->name);

$output = $PAGE->get_renderer('mod_attendance');
echo $output->header();
$mform->display();
echo $output->footer();
