<?php


require_once("../../config.php");
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/scorm/locallib.php');
require_once($CFG->dirroot.'/mod/scorm/reportsettings_form.php');
require_once($CFG->dirroot.'/mod/scorm/report/reportlib.php');
require_once($CFG->libdir.'/formslib.php');

define('SCORM_REPORT_DEFAULT_PAGE_SIZE', 20);
define('SCORM_REPORT_ATTEMPTS_ALL_STUDENTS', 0);
define('SCORM_REPORT_ATTEMPTS_STUDENTS_WITH', 1);
define('SCORM_REPORT_ATTEMPTS_STUDENTS_WITH_NO', 2);

$id = required_param('id', PARAM_INT);$download = optional_param('download', '', PARAM_RAW);
$mode = optional_param('mode', '', PARAM_ALPHA); 
$cm = get_coursemodule_from_id('scorm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$scorm = $DB->get_record('scorm', array('id' => $cm->instance), '*', MUST_EXIST);

$contextmodule = context_module::instance($cm->id);
$reportlist = scorm_report_list($contextmodule);

$url = new moodle_url('/mod/scorm/report.php');

$url->param('id', $id);
if (empty($mode)) {
    $mode = reset($reportlist);
} else if (!in_array($mode, $reportlist)) {
    print_error('erroraccessingreport', 'scorm');
}
$url->param('mode', $mode);

$PAGE->set_url($url);

require_login($course, false, $cm);
$PAGE->set_pagelayout('report');

require_capability('mod/scorm:viewreport', $contextmodule);

if (count($reportlist) < 1) {
    print_error('erroraccessingreport', 'scorm');
}

$event = \mod_scorm\event\report_viewed::create(array(
    'context' => $contextmodule,
    'other' => array(
        'scormid' => $scorm->id,
        'mode' => $mode
    )
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('scorm', $scorm);
$event->trigger();

$userdata = null;
if (!empty($download)) {
    $noheader = true;
}
if (empty($noheader)) {
    $strreport = get_string('report', 'scorm');
    $strattempt = get_string('attempt', 'scorm');

    $PAGE->set_title("$course->shortname: ".format_string($scorm->name));
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add($strreport, new moodle_url('/mod/scorm/report.php', array('id' => $cm->id)));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($scorm->name));
    $currenttab = 'reports';
    require($CFG->dirroot . '/mod/scorm/tabs.php');
}

$classname = "scormreport_{$mode}\\report";
$legacyclassname = "scorm_{$mode}_report";
$report = class_exists($classname) ? new $classname() : new $legacyclassname();
$report->display($scorm, $cm, $course, $download); 

if (empty($noheader)) {
    echo $OUTPUT->footer();
}
