<?php

require_once($CFG->dirroot.'/mod/scorm/locallib.php');

$userdata = new stdClass();
$def = new stdClass();
$cmiobj = new stdClass();

if (!isset($currentorg)) {
    $currentorg = '';
}

if ($scoes = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id), 'sortorder, id')) {
        $scoes = array_values($scoes);
    foreach ($scoes as $sco) {
        $def->{($sco->id)} = new stdClass();
        $userdata->{($sco->id)} = new stdClass();
        $def->{($sco->id)} = get_scorm_default($userdata->{($sco->id)}, $scorm, $sco->id, $attempt, $mode);

                $cmiobj->{($sco->id)} = '';
        $currentobj = '';
        $count = 0;
        foreach ($userdata as $element => $value) {
            if (substr($element, 0, 14) == 'cmi.objectives') {
                $element = preg_replace('/\.(\d+)\./', "_\$1.", $element);
                preg_match('/\_(\d+)\./', $element, $matches);
                if (count($matches) > 0 && $currentobj != $matches[1]) {
                    $currentobj = $matches[1];
                    $count++;
                    $end = strpos($element, $matches[1]) + strlen($matches[1]);
                    $subelement = substr($element, 0, $end);
                    $cmiobj->{($sco->id)} .= '    '.$subelement." = new Object();\n";
                    $cmiobj->{($sco->id)} .= '    '.$subelement.".score = new Object();\n";
                    $cmiobj->{($sco->id)} .= '    '.$subelement.".score._children = score_children;\n";
                    $cmiobj->{($sco->id)} .= '    '.$subelement.".score.raw = '';\n";
                    $cmiobj->{($sco->id)} .= '    '.$subelement.".score.min = '';\n";
                    $cmiobj->{($sco->id)} .= '    '.$subelement.".score.max = '';\n";
                }
                $cmiobj->{($sco->id)} .= '    '.$element.' = \''.$value."';\n";
            }
        }
        if ($count > 0) {
            $cmiobj->{($sco->id)} .= '    cmi.objectives._count = '.$count.";\n";
        }
    }
}


$PAGE->requires->js_init_call('M.scorm_api.init', array($def, $cmiobj, $scorm->auto, $CFG->wwwroot, $scorm->id, $scoid,
                                                            $attempt, $mode, $currentorg, sesskey(), $id, $scorm->autocommit));
