<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/scheduler/lib.php');
require_once($CFG->dirroot.'/mod/scheduler/locallib.php');
require_once($CFG->dirroot.'/mod/scheduler/renderable.php');

$id = optional_param('id', '', PARAM_INT);    $action = optional_param('what', 'view', PARAM_ALPHA);
$subaction = optional_param('subaction', '', PARAM_ALPHA);
$offset = optional_param('offset', -1, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('scheduler', $id, 0, false, MUST_EXIST);
    $scheduler = scheduler_instance::load_by_coursemodule_id($id);
} else {
    $a = required_param('a', PARAM_INT);         $scheduler = scheduler_instance::load_by_id($a);
    $cm = $scheduler->get_cm();
}
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$defaultsubpage = groups_get_activity_groupmode($cm) ? 'myappointments' : 'allappointments';
$subpage = optional_param('subpage', $defaultsubpage, PARAM_ALPHA);

require_login($course->id, false, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/scheduler/view.php', array('id' => $cm->id));

$output = $PAGE->get_renderer('mod_scheduler');


$title = $course->shortname . ': ' . format_string($scheduler->name);
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);



$isteacher = has_capability('mod/scheduler:manage', $context);
$isstudent = has_capability('mod/scheduler:viewslots', $context);
if ($isteacher) {
        if ($action == 'viewstatistics') {
        include($CFG->dirroot.'/mod/scheduler/viewstatistics.php');
    } else if ($action == 'viewstudent') {
        include($CFG->dirroot.'/mod/scheduler/viewstudent.php');
    } else if ($action == 'export') {
        include($CFG->dirroot.'/mod/scheduler/export.php');
    } else if ($action == 'datelist') {
        include($CFG->dirroot.'/mod/scheduler/datelist.php');
    } else {
        include($CFG->dirroot.'/mod/scheduler/teacherview.php');
    }

} else if ($isstudent) {
        include($CFG->dirroot.'/mod/scheduler/studentview.php');

} else {
        echo $OUTPUT->header();
    echo $OUTPUT->box(get_string('guestscantdoanything', 'scheduler'), 'generalbox');
    echo $OUTPUT->footer($course);
}
