<?php

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/scorm/locallib.php');

$id = optional_param('id', '', PARAM_INT);  $a = optional_param('a', '', PARAM_INT);  $scoid = required_param('scoid', PARAM_INT);  $attempt = required_param('attempt', PARAM_INT);  $function  = required_param('function', PARAM_RAW);  $request = optional_param('request', '', PARAM_RAW);  
if (!empty($id)) {
    $cm = get_coursemodule_from_id('scorm', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record("course", array("id" => $cm->course), '*', MUST_EXIST);
    $scorm = $DB->get_record("scorm", array("id" => $cm->instance), '*', MUST_EXIST);
} else if (!empty($a)) {
    $scorm = $DB->get_record("scorm", array("id" => $a), '*', MUST_EXIST);
    $course = $DB->get_record("course", array("id" => $scorm->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance("scorm", $scorm->id, $course->id, false, MUST_EXIST);
} else {
    print_error('missingparameter');
}

$PAGE->set_url('/mod/scorm/datamodels/sequencinghandler.php',
    array('scoid' => $scoid, 'attempt' => $attempt, 'id' => $cm->id, 'function' => $function, 'request' => $request));

require_login($course, false, $cm);

if (!empty($scoid) && !empty($function)) {
    require_once($CFG->dirroot.'/mod/scorm/datamodels/scorm_13lib.php');

    if (has_capability('mod/scorm:savetrack', context_module::instance($cm->id))) {
        $result = null;
        switch ($function) {
            case 'scorm_seq_flow' :
                if ($request == 'forward' || $request == 'backward') {
                    $seq = scorm_seq_navigation ($scoid, $USER->id, $request.'_', $attempt);
                    $sco = scorm_get_sco($scoid);
                    $seq = scorm_seq_flow($sco, $request, $seq, true, $USER->id);
                    if (!empty($seq->nextactivity)) {
                        scorm_seq_end_attempt($sco, $USER->id, $seq);
                    }
                }
                echo json_encode($seq);
                break;
        }
    }
}
