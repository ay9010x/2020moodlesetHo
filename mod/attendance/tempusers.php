<?php



require_once(dirname(__FILE__).'/../../config.php');
global $CFG, $DB, $OUTPUT, $PAGE, $COURSE;
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/mod/attendance/temp_form.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

$att = new mod_attendance_structure($att, $cm, $course);
$PAGE->set_url($att->url_managetemp());

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/attendance:managetemporaryusers', $context);

$PAGE->set_title($course->shortname.": ".$att->name.' - '.get_string('tempusers', 'attendance'));
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->navbar->add(get_string('tempusers', 'attendance'));

$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att, attendance_tabs::TAB_TEMPORARYUSERS);

$formdata = (object)array(
    'id' => $cm->id,
);
$mform = new temp_form();
$mform->set_data($formdata);

if ($data = $mform->get_data()) {
        $user = new stdClass();
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->deleted = 1;
    $user->email = time().'@ghost.user.de';
    $user->username = time().'@ghost.user.de';
    $user->idnumber = 'tempghost';
    $user->mnethostid = $CFG->mnet_localhost_id;
    $studentid = $DB->insert_record('user', $user);

        $newtempuser = new stdClass();
    $newtempuser->fullname = $data->tname;
    $newtempuser->courseid = $COURSE->id;
    $newtempuser->email = $data->temail;
    $newtempuser->created = time();
    $newtempuser->studentid = $studentid;
    $DB->insert_record('attendance_tempusers', $newtempuser);

    redirect($att->url_managetemp());
}

echo $output->header();
echo $output->heading(get_string('tempusers', 'attendance').' : '.format_string($course->fullname));
echo $output->render($tabs);
$mform->display();

$tempusers = $DB->get_records('attendance_tempusers', array('courseid' => $course->id), 'fullname, email');

echo '<div>';
echo '<p style="margin-left:10%;">'.get_string('tempuserslist', 'attendance').'</p>';
if ($tempusers) {
    print_tempusers($tempusers, $att);
}
echo '</div>';
echo $output->footer($course);

function print_tempusers($tempusers, mod_attendance_structure $att) {
    echo '<p></p>';
    echo '<table border="1" bordercolor="#EEEEEE" style="background-color:#fff" cellpadding="2" align="center"'.
          'width="80%" summary="'.get_string('temptable', 'attendance').'"><tr>';
    echo '<th class="header">'.get_string('tusername', 'attendance').'</th>';
    echo '<th class="header">'.get_string('tuseremail', 'attendance').'</th>';
    echo '<th class="header">'.get_string('tcreated', 'attendance').'</th>';
    echo '<th class="header">'.get_string('tactions', 'attendance').'</th>';
    echo '</tr>';

    $even = false;     foreach ($tempusers as $tempuser) {
        if ($even) {
            echo '<tr style="background-color: #FCFCFC">';
        } else {
            echo '<tr>';
        }
        $even = !$even;
        echo '<td>'.format_string($tempuser->fullname).'</td>';
        echo '<td>'.format_string($tempuser->email).'</td>';
        echo '<td>'.userdate($tempuser->created, get_string('strftimedatetime')).'</td>';
        $params = array('userid' => $tempuser->id);
        $editlink = html_writer::link($att->url_tempedit($params), get_string('edituser', 'attendance'));
        $deletelink = html_writer::link($att->url_tempdelete($params), get_string('deleteuser', 'attendance'));
        $mergelink = html_writer::link($att->url_tempmerge($params), get_string('mergeuser', 'attendance'));
        echo '<td>'.$editlink.' | '.$deletelink.' | '.$mergelink.'</td>';
        echo '</tr>';
    }
    echo '</table>';
}


