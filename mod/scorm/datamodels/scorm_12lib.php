<?php



function get_scorm_default (&$userdata, $scorm, $scoid, $attempt, $mode) {
    global $USER;

    $userdata->student_id = $USER->username;
    $userdata->student_name = $USER->lastname .', '. $USER->firstname;

    if ($usertrack = scorm_get_tracks($scoid, $USER->id, $attempt)) {
        foreach ($usertrack as $key => $value) {
            $userdata->$key = $value;
        }
    } else {
        $userdata->status = '';
        $userdata->score_raw = '';
    }

    if ($scodatas = scorm_get_sco($scoid, SCO_DATA)) {
        foreach ($scodatas as $key => $value) {
            $userdata->$key = $value;
        }
    } else {
        print_error('cannotfindsco', 'scorm');
    }
    if (!$sco = scorm_get_sco($scoid)) {
        print_error('cannotfindsco', 'scorm');
    }

    if (isset($userdata->status)) {
        if ($userdata->status == '') {
            $userdata->entry = 'ab-initio';
        } else {
            if (isset($userdata->{'cmi.core.exit'}) && ($userdata->{'cmi.core.exit'} == 'suspend')) {
                $userdata->entry = 'resume';
            } else {
                $userdata->entry = '';
            }
        }
    }

    $userdata->mode = 'normal';
    if (!empty($mode)) {
        $userdata->mode = $mode;
    }
    if ($userdata->mode == 'normal') {
        $userdata->credit = 'credit';
    } else {
        $userdata->credit = 'no-credit';
    }

    $def = array();
    $def['cmi.core.student_id'] = $userdata->student_id;
    $def['cmi.core.student_name'] = $userdata->student_name;
    $def['cmi.core.credit'] = $userdata->credit;
    $def['cmi.core.entry'] = $userdata->entry;
    $def['cmi.core.lesson_mode'] = $userdata->mode;
    $def['cmi.launch_data'] = scorm_isset($userdata, 'datafromlms');
    $def['cmi.student_data.mastery_score'] = scorm_isset($userdata, 'masteryscore');
    $def['cmi.student_data.max_time_allowed'] = scorm_isset($userdata, 'maxtimeallowed');
    $def['cmi.student_data.time_limit_action'] = scorm_isset($userdata, 'timelimitaction');
    $def['cmi.core.total_time'] = scorm_isset($userdata, 'cmi.core.total_time', '00:00:00');

        $def['cmi.core.lesson_location'] = scorm_isset($userdata, 'cmi.core.lesson_location');
    $def['cmi.core.lesson_status'] = scorm_isset($userdata, 'cmi.core.lesson_status');
    $def['cmi.core.score.raw'] = scorm_isset($userdata, 'cmi.core.score.raw');
    $def['cmi.core.score.max'] = scorm_isset($userdata, 'cmi.core.score.max');
    $def['cmi.core.score.min'] = scorm_isset($userdata, 'cmi.core.score.min');
    $def['cmi.core.exit'] = scorm_isset($userdata, 'cmi.core.exit');
    $def['cmi.suspend_data'] = scorm_isset($userdata, 'cmi.suspend_data');
    $def['cmi.comments'] = scorm_isset($userdata, 'cmi.comments');
    $def['cmi.student_preference.language'] = scorm_isset($userdata, 'cmi.student_preference.language');
    $def['cmi.student_preference.audio'] = scorm_isset($userdata, 'cmi.student_preference.audio', '0');
    $def['cmi.student_preference.speed'] = scorm_isset($userdata, 'cmi.student_preference.speed', '0');
    $def['cmi.student_preference.text'] = scorm_isset($userdata, 'cmi.student_preference.text', '0');
    return $def;
}
