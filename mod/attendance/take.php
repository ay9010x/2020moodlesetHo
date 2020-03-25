<?php



require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

$pageparams = new mod_attendance_take_page_params();

$id                     = required_param('id', PARAM_INT);
$pageparams->sessionid  = required_param('sessionid', PARAM_INT);
$pageparams->grouptype  = required_param('grouptype', PARAM_INT);
$pageparams->sort       = optional_param('sort', null, PARAM_INT);
$pageparams->copyfrom   = optional_param('copyfrom', null, PARAM_INT);
$pageparams->viewmode   = optional_param('viewmode', null, PARAM_INT);
$pageparams->gridcols   = optional_param('gridcols', null, PARAM_INT);
$pageparams->page       = optional_param('page', 1, PARAM_INT);
$pageparams->perpage    = optional_param('perpage', get_config('attendance', 'resultsperpage'), PARAM_INT);

$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/attendance:takeattendances', $context);

$pageparams->group = groups_get_activity_group($cm, true);

$pageparams->init($course->id);
$att = new mod_attendance_structure($att, $cm, $course, $PAGE->context, $pageparams);

$allowedgroups = groups_get_activity_allowed_groups($cm);
if (!empty($pageparams->grouptype) && !array_key_exists($pageparams->grouptype, $allowedgroups)) {
     $group = groups_get_group($pageparams->grouptype);
     throw new moodle_exception('cannottakeforgroup', 'attendance', '', $group->name);
}

if (($formdata = data_submitted()) && confirm_sesskey()) {
    $att->take_from_form_data($formdata);
}

$PAGE->set_url($att->url_take());
$PAGE->set_title($course->shortname. ": ".$att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add($att->name);

$output = $PAGE->get_renderer('mod_attendance');
$tabs = new attendance_tabs($att);
$sesstable = new attendance_take_data($att);


echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'attendance').' :: ' .format_string($course->fullname));
echo $output->render($tabs);
echo $output->render($sesstable);

echo $output->footer();
