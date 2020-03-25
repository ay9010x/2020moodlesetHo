<?php


require_once('../../config.php');
require_once($CFG->dirroot.'/mod/scorm/locallib.php');

$id = optional_param('id', '', PARAM_INT);                  $a = optional_param('a', '', PARAM_INT);                    $scoid = required_param('scoid', PARAM_INT);                $attempt = required_param('attempt', PARAM_INT);            $mode = optional_param('mode', 'normal', PARAM_ALPHA);      $currentorg = optional_param('currentorg', '', PARAM_RAW);  
if (!empty($id)) {
    if (! $cm = get_coursemodule_from_id('scorm', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error('coursemisconf');
    }
    if (! $scorm = $DB->get_record("scorm", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
} else if (!empty($a)) {
    if (! $scorm = $DB->get_record("scorm", array("id" => $a))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id" => $scorm->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("scorm", $scorm->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
} else {
    print_error('missingparameter');
}

if (!empty($currentorg)) {
    if (!$DB->record_exists('scorm_scoes', array('scorm' => $scorm->id, 'identifier' => $currentorg))) {
        $currentorg = '';
    }
}

$PAGE->set_url('/mod/scorm/prereqs.php', array('scoid' => $scoid, 'attempt' => $attempt, 'id' => $cm->id));

require_login($course, false, $cm);

$scorm->version = strtolower(clean_param($scorm->version, PARAM_SAFEDIR));   if (!file_exists($CFG->dirroot.'/mod/scorm/datamodels/'.$scorm->version.'lib.php')) {
    $scorm->version = 'scorm_12';
}
require_once($CFG->dirroot.'/mod/scorm/datamodels/'.$scorm->version.'lib.php');


if (confirm_sesskey() && (!empty($scoid))) {
    $result = true;
    $request = null;
    if (has_capability('mod/scorm:savetrack', context_module::instance($cm->id))) {
        $result = scorm_get_toc($USER, $scorm, $cm->id, TOCJSLINK, $currentorg, $scoid, $mode, $attempt, true, false);
        echo $result->toc;
    }
}
