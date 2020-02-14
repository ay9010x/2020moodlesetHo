<?php




require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$pageparams = new mod_attendance_view_page_params();

$id                     = required_param('id', PARAM_INT);
$pageparams->studentid  = optional_param('studentid', null, PARAM_INT);
$pageparams->mode       = optional_param('mode', mod_attendance_view_page_params::MODE_THIS_COURSE, PARAM_INT);
$pageparams->view       = optional_param('view', null, PARAM_INT);
$pageparams->curdate    = optional_param('curdate', null, PARAM_INT);

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$attendance    = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/attendance:view', $context);

$pageparams->init($cm);
$att = new mod_attendance_structure($attendance, $cm, $course, $context, $pageparams);

if (!$pageparams->studentid) {
    $capabilities = array(
        'mod/attendance:manageattendances',
        'mod/attendance:takeattendances',
        'mod/attendance:changeattendances'
    );
    if (has_any_capability($capabilities, $context)) {
        redirect($att->url_manage());
    } else if (has_capability('mod/attendance:viewreports', $context)) {
        redirect($att->url_report());
    }
}

$PAGE->set_url($att->url_view());
$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->navbar->add(get_string('attendancereport', 'attendance'));

$output = $PAGE->get_renderer('mod_attendance');

if (isset($pageparams->studentid) && $USER->id != $pageparams->studentid) {
        require_capability('mod/attendance:viewreports', $context);
    $userid = $pageparams->studentid;
} else {
        $userid = $USER->id;
}

$userdata = new attendance_user_data($att, $userid);

echo $output->header();

echo $output->render($userdata);

echo $output->footer();
