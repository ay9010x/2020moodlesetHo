<?php



require_once(dirname(__FILE__).'/../../config.php');

global $CFG, $DB, $PAGE, $OUTPUT;
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/mod/attendance/tempmerge_form.php');

$id = required_param('id', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);
$tempuser = $DB->get_record('attendance_tempusers', array('id' => $userid), '*', MUST_EXIST);

$att = new mod_attendance_structure($att, $cm, $course);
$params = array('userid' => $tempuser->id);
$PAGE->set_url($att->url_tempmerge($params));

require_login($course, true, $cm);

$PAGE->set_title($course->shortname.": ".$att->name.' - '.get_string('tempusermerge', 'attendance'));
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add(get_string('tempusermerge', 'attendance'));

$formdata = (object)array(
    'id' => $cm->id,
    'userid' => $tempuser->id,
);

$custom = array(
    'description' => format_string($tempuser->fullname).' ('.format_string($tempuser->email).')',
);
$mform = new tempmerge_form(null, $custom);
$mform->set_data($formdata);

if ($mform->is_cancelled()) {
    redirect($att->url_managetemp());

} else if ($data = $mform->get_data()) {

    $sql = "SELECT s.id, lr.id AS reallogid, lt.id AS templogid
              FROM {attendance_sessions} s
              LEFT JOIN {attendance_log} lr ON lr.sessionid = s.id AND lr.studentid = :realuserid
              LEFT JOIN {attendance_log} lt ON lt.sessionid = s.id AND lt.studentid = :tempuserid
             WHERE s.attendanceid = :attendanceid AND lt.id IS NOT NULL
             ORDER BY s.id";
    $params = array(
        'realuserid' => $data->participant,
        'tempuserid' => $tempuser->studentid,
        'attendanceid' => $att->id,
    );
    $logs = $DB->get_recordset_sql($sql, $params);

    foreach ($logs as $log) {
        if (!is_null($log->reallogid)) {
                        $DB->delete_records('attendance_log', array('id' => $log->reallogid));
        }
                $DB->set_field('attendance_log', 'studentid', $data->participant, array('id' => $log->templogid));
    }

        $DB->delete_records('attendance_tempusers', array('id' => $tempuser->id));
    $att->update_users_grade(array($data->participant)); 
    redirect($att->url_managetemp());
}


$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att, attendance_tabs::TAB_TEMPORARYUSERS);

echo $output->header();
echo $output->heading(get_string('tempusermerge', 'attendance').' : '.format_string($course->fullname));
echo $output->render($tabs);
$mform->display();
echo $output->footer($course);