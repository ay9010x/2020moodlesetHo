<?php

require_once($CFG->dirroot.'/mod/scorm/locallib.php');

$userdata = new stdClass();
$def = new stdClass();
$cmiobj = new stdClass();
$cmiint = new stdClass();
$cmicommentsuser = new stdClass();
$cmicommentslms = new stdClass();

if (!isset($currentorg)) {
    $currentorg = '';
}

if ($scoes = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id), 'sortorder, id')) {
        $scoes = array_values($scoes);
    foreach ($scoes as $sco) {
        $def->{($sco->id)} = new stdClass();
        $userdata->{($sco->id)} = new stdClass();
        $def->{($sco->id)} = get_scorm_default($userdata->{($sco->id)}, $scorm, $sco->id, $attempt, $mode);

                $cmiobj->{($sco->id)} = scorm_reconstitute_array_element($scorm->version, $userdata->{($sco->id)},
                                                                    'cmi.objectives', array('score'));
        $cmiint->{($sco->id)} = scorm_reconstitute_array_element($scorm->version, $userdata->{($sco->id)},
                                                                    'cmi.interactions', array('objectives', 'correct_responses'));
        $cmicommentsuser->{($sco->id)} = scorm_reconstitute_array_element($scorm->version, $userdata->{($sco->id)},
                                                                    'cmi.comments_from_learner', array());
        $cmicommentslms->{($sco->id)} = scorm_reconstitute_array_element($scorm->version, $userdata->{($sco->id)},
                                                                    'cmi.comments_from_lms', array());
    }
}

$scorm->autocommit = ($scorm->autocommit === "1") ? true : false;
$PAGE->requires->js_init_call('M.scorm_api.init', array($def, $cmiobj, $cmiint, $cmicommentsuser, $cmicommentslms,
                                                        scorm_debugging($scorm), $scorm->auto, $scorm->id, $CFG->wwwroot,
                                                        sesskey(), $scoid, $attempt, $mode, $id, $currentorg, $scorm->autocommit));


if (scorm_debugging($scorm)) {
    require_once($CFG->dirroot.'/mod/scorm/datamodels/debug.js.php');
    echo html_writer::script('AppendToLog("Moodle SCORM 1.3 API Loaded, Activity: '.
                                $scorm->name.', SCO: '.$sco->identifier.'", 0);');
}
