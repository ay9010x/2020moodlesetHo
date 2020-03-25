<?php



require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$pageparams = new mod_attendance_manage_page_params();

$id                         = required_param('id', PARAM_INT);
$from                       = optional_param('from', null, PARAM_ALPHANUMEXT);
$pageparams->view           = optional_param('view', null, PARAM_INT);
$pageparams->curdate        = optional_param('curdate', null, PARAM_INT);
$pageparams->perpage        = get_config('attendance', 'resultsperpage');

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$capabilities = array(
    'mod/attendance:manageattendances',
    'mod/attendance:takeattendances',
    'mod/attendance:changeattendances'
);
if (!has_any_capability($capabilities, $context)) {
    redirect($att->url_view());
}

$pageparams->init($cm);
$att = new mod_attendance_structure($att, $cm, $course, $context, $pageparams);

if ($from === 'block') {
    $sessions = $att->get_today_sessions();
    $size = count($sessions);
    if ($size == 1) {
        $sess = reset($sessions);
        $nottaken = !$sess->lasttaken && has_capability('mod/attendance:takeattendances', $context);
        $canchange = $sess->lasttaken && has_capability('mod/attendance:changeattendances', $context);
        if ($nottaken || $canchange) {
            redirect($att->url_take(array('sessionid' => $sess->id, 'grouptype' => $sess->groupid)));
        }
    } else if ($size > 1) {
        $att->curdate = $today;
                $att->view = ATT_VIEW_DAYS;
    }
}

$PAGE->set_url($att->url_manage());
$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add($att->name);

$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att, attendance_tabs::TAB_SESSIONS);
$filtercontrols = new attendance_filter_controls($att);
$sesstable = new attendance_manage_data($att);


echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'attendance').' :: ' .format_string($course->fullname));
mod_attendance_notifyqueue::show();
echo $output->render($tabs);
echo $output->render($filtercontrols);
echo $output->render($sesstable);

echo $output->footer();

