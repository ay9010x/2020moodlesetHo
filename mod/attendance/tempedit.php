<?php



require_once(dirname(__FILE__).'/../../config.php');

global $CFG, $DB, $PAGE, $OUTPUT;
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/mod/attendance/tempedit_form.php');

$id = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);

$cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
$tempuser = $DB->get_record('attendance_tempusers', array('id' => $userid), '*', MUST_EXIST);

$att = new mod_attendance_structure($att, $cm, $course);

$params = array('userid' => $tempuser->id);
if ($action) {
    $params['action'] = $action;
}
$PAGE->set_url($att->url_tempedit($params));

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/attendance:managetemporaryusers', $context);

$PAGE->set_title($course->shortname.": ".$att->name.' - '.get_string('tempusersedit', 'attendance'));
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->navbar->add(get_string('tempusersedit', 'attendance'));


$output = $PAGE->get_renderer('mod_attendance');

if ($action == 'delete') {
    if (optional_param('confirm', false, PARAM_BOOL)) {
        require_sesskey();

                $DB->delete_records('grade_grades', array('userid' => $tempuser->studentid));
        $DB->delete_records('attendance_log', array('studentid' => $tempuser->studentid));
        $DB->delete_records('attendance_tempusers', array('id' => $tempuser->id));

        redirect($att->url_managetemp());
    } else {

        $info = (object)array(
            'fullname' => $tempuser->fullname,
            'email' => $tempuser->email,
        );
        $msg = get_string('confirmdeleteuser', 'attendance', $info);
        $continue = new moodle_url($PAGE->url, array('confirm' => 1, 'sesskey' => sesskey()));

        echo $output->header();
        echo $output->confirm($msg, $continue, $att->url_managetemp());
        echo $output->footer();

        die();
    }
}

$formdata = new stdClass();
$formdata->id = $cm->id;
$formdata->tname = $tempuser->fullname;
$formdata->userid = $tempuser->id;
$formdata->temail = $tempuser->email;

$mform = new tempedit_form();
$mform->set_data($formdata);

if ($mform->is_cancelled()) {
    redirect($att->url_managetemp());
} else if ($tempuser = $mform->get_data()) {
    global $DB;
    $updateuser = new stdClass();
    $updateuser->id = $tempuser->userid;
    $updateuser->fullname = $tempuser->tname;
    $updateuser->email = $tempuser->temail;
    $DB->update_record('attendance_tempusers', $updateuser);
    redirect($att->url_managetemp());
}

$tabs = new attendance_tabs($att, attendance_tabs::TAB_TEMPORARYUSERS);

echo $output->header();
echo $output->heading(get_string('tempusersedit', 'attendance').' : '.format_string($course->fullname));
echo $output->render($tabs);
$mform->display();
echo $output->footer($course);

