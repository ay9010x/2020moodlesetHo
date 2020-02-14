<?php




require('../../config.php');
require_once("$CFG->dirroot/mod/folder/locallib.php");
require_once("$CFG->dirroot/repository/lib.php");
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);  $f  = optional_param('f', 0, PARAM_INT);   
if ($f) {      $folder = $DB->get_record('folder', array('id'=>$f), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('folder', $folder->id, $folder->course, true, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('folder', $id, 0, true, MUST_EXIST);
    $folder = $DB->get_record('folder', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/folder:view', $context);
if ($folder->display == FOLDER_DISPLAY_INLINE) {
    redirect(course_get_url($folder->course, $cm->sectionnum));
}

$params = array(
    'context' => $context,
    'objectid' => $folder->id
);
$event = \mod_folder\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('folder', $folder);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/folder/view.php', array('id' => $cm->id));

$PAGE->set_title($course->shortname.': '.$folder->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($folder);


$output = $PAGE->get_renderer('mod_folder');

echo $output->header();

echo $output->heading(format_string($folder->name), 2);

echo $output->display_folder($folder);

echo $output->footer();
