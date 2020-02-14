<?php



function scorm_add_time($a, $b) {
    $aes = explode(':', $a);
    $bes = explode(':', $b);
    $aseconds = explode('.', $aes[2]);
    $bseconds = explode('.', $bes[2]);
    $change = 0;

    $acents = 0;      if (count($aseconds) > 1) {
        $acents = $aseconds[1];
    }
    $bcents = 0;
    if (count($bseconds) > 1) {
        $bcents = $bseconds[1];
    }
    $cents = $acents + $bcents;
    $change = floor($cents / 100);
    $cents = $cents - ($change * 100);
    if (floor($cents) < 10) {
        $cents = '0'. $cents;
    }

    $secs = $aseconds[0] + $bseconds[0] + $change;      $change = floor($secs / 60);
    $secs = $secs - ($change * 60);
    if (floor($secs) < 10) {
        $secs = '0'. $secs;
    }

    $mins = $aes[1] + $bes[1] + $change;       $change = floor($mins / 60);
    $mins = $mins - ($change * 60);
    if ($mins < 10) {
        $mins = '0' .  $mins;
    }

    $hours = $aes[0] + $bes[0] + $change;      if ($hours < 10) {
        $hours = '0' . $hours;
    }

    if ($cents != '0') {
        return $hours . ":" . $mins . ":" . $secs . '.' . $cents;
    } else {
        return $hours . ":" . $mins . ":" . $secs;
    }
}


function scorm_get_aicc_columns($row, $mastername='system_id') {
    $tok = strtok(strtolower($row), "\",\n\r");
    $result = new stdClass();
    $result->columns = array();
    $result->mastercol = 0;
    $i = 0;
    while ($tok) {
        if ($tok != '') {
            $result->columns[] = $tok;
            if ($tok == $mastername) {
                $result->mastercol = $i;
            }
            $i++;
        }
        $tok = strtok("\",\n\r");
    }
    return $result;
}


function scorm_forge_cols_regexp($columns, $remodule='(".*")?,') {
    $regexp = '/^';
    foreach ($columns as $column) {
        $regexp .= $remodule;
    }
    $regexp = substr($regexp, 0, -1) . '/';
    return $regexp;
}


function scorm_parse_aicc(&$scorm) {
    global $DB;

    if ($scorm->scormtype == SCORM_TYPE_AICCURL) {
        return scorm_aicc_generate_simple_sco($scorm);
    }
    if (!isset($scorm->cmid)) {
        $cm = get_coursemodule_from_instance('scorm', $scorm->id);
        $scorm->cmid = $cm->id;
    }
    $context = context_module::instance($scorm->cmid);

    $fs = get_file_storage();

    $files = $fs->get_area_files($context->id, 'mod_scorm', 'content', 0, 'sortorder, itemid, filepath, filename', false);

    $version = 'AICC';
    $ids = array();
    $courses = array();
    $extaiccfiles = array('crs', 'des', 'au', 'cst', 'ort', 'pre', 'cmp');

    foreach ($files as $file) {
        $filename = $file->get_filename();
        $ext = substr($filename, strrpos($filename, '.'));
        $extension = strtolower(substr($ext, 1));
        if (in_array($extension, $extaiccfiles)) {
            $id = strtolower(basename($filename, $ext));
            if (!isset($ids[$id])) {
                $ids[$id] = new stdClass();
            }
            $ids[$id]->$extension = $file;
        }
    }

    foreach ($ids as $courseid => $id) {
        if (!isset($courses[$courseid])) {
            $courses[$courseid] = new stdClass();
        }
        if (isset($id->crs)) {
            $contents = $id->crs->get_content();
            $rows = explode("\r\n", $contents);
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    if (preg_match("/^(.+)=(.+)$/", $row, $matches)) {
                        switch (strtolower(trim($matches[1]))) {
                            case 'course_id':
                                $courses[$courseid]->id = trim($matches[2]);
                            break;
                            case 'course_title':
                                $courses[$courseid]->title = trim($matches[2]);
                            break;
                            case 'version':
                                $courses[$courseid]->version = 'AICC_'.trim($matches[2]);
                            break;
                        }
                    }
                }
            }
        }
        if (isset($id->des)) {
            $contents = $id->des->get_content();
            $rows = explode("\r\n", $contents);
            $columns = scorm_get_aicc_columns($rows[0]);
            $regexp = scorm_forge_cols_regexp($columns->columns);
            for ($i = 1; $i < count($rows); $i++) {
                if (preg_match($regexp, $rows[$i], $matches)) {
                    for ($j = 0; $j < count($columns->columns); $j++) {
                        $column = $columns->columns[$j];
                        if (!isset($courses[$courseid]->elements[substr(trim($matches[$columns->mastercol + 1]), 1 , -1)])) {
                            $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol + 1]), 1 , -1)] = new stdClass();
                        }
                        $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol + 1]), 1 , -1)]->$column = substr(trim($matches[$j + 1]), 1, -1);
                    }
                }
            }
        }
        if (isset($id->au)) {
            $contents = $id->au->get_content();
            $rows = explode("\r\n", $contents);
            $columns = scorm_get_aicc_columns($rows[0]);
            $regexp = scorm_forge_cols_regexp($columns->columns);
            for ($i = 1; $i < count($rows); $i++) {
                if (preg_match($regexp, $rows[$i], $matches)) {
                    for ($j = 0; $j < count($columns->columns); $j++) {
                        $column = $columns->columns[$j];
                        $courses[$courseid]->elements[substr(trim($matches[$columns->mastercol + 1]), 1, -1)]->$column = substr(trim($matches[$j + 1]), 1, -1);
                    }
                }
            }
        }
        if (isset($id->cst)) {
            $contents = $id->cst->get_content();
            $rows = explode("\r\n", $contents);
            $columns = scorm_get_aicc_columns($rows[0], 'block');
            $regexp = scorm_forge_cols_regexp($columns->columns, '(.+)?,');
            for ($i = 1; $i < count($rows); $i++) {
                if (preg_match($regexp, $rows[$i], $matches)) {
                    for ($j = 0; $j < count($columns->columns); $j++) {
                        if ($j != $columns->mastercol) {
                            $element = substr(trim($matches[$j + 1]), 1 , -1);
                            if (!empty($element)) {
                                $courses[$courseid]->elements[$element]->parent = substr(trim($matches[$columns->mastercol + 1]), 1, -1);
                            }
                        }
                    }
                }
            }
        }
        if (isset($id->ort)) {
            $contents = $id->ort->get_content();
            $rows = explode("\r\n", $contents);
            $columns = scorm_get_aicc_columns($rows[0], 'course_element');
            $regexp = scorm_forge_cols_regexp($columns->columns, '(.+)?,');
            for ($i = 1; $i < count($rows); $i++) {
                if (preg_match($regexp, $rows[$i], $matches)) {
                    for ($j = 0; $j < count($matches) - 1; $j++) {
                        if ($j != $columns->mastercol) {
                            $courses[$courseid]->elements[substr(trim($matches[$j + 1]), 1, -1)]->parent = substr(trim($matches[$columns->mastercol + 1]), 1, -1);
                        }
                    }
                }
            }
        }
        if (isset($id->pre)) {
            $contents = $id->pre->get_content();
            $rows = explode("\r\n", $contents);
            $columns = scorm_get_aicc_columns($rows[0], 'structure_element');
            $regexp = scorm_forge_cols_regexp($columns->columns, '(.+),');
            for ($i = 1; $i < count($rows); $i++) {
                if (preg_match($regexp, $rows[$i], $matches)) {
                    $elementid = trim($matches[$columns->mastercol + 1]);
                    $elementid = trim(trim($elementid, '"'), "'"); 
                    $prereq = trim($matches[2 - $columns->mastercol]);
                    $prereq = trim(trim($prereq, '"'), "'"); 
                    $courses[$courseid]->elements[$elementid]->prerequisites = $prereq;
                }
            }
        }
        if (isset($id->cmp)) {
            $contents = $id->cmp->get_content();
            $rows = explode("\r\n", $contents);
        }
    }

    $oldscoes = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id));
    $sortorder = 0;
    $launch = 0;
    if (isset($courses)) {
        foreach ($courses as $course) {
            $sortorder++;
            $sco = new stdClass();
            $sco->identifier = $course->id;
            $sco->scorm = $scorm->id;
            $sco->organization = '';
            $sco->title = $course->title;
            $sco->parent = '/';
            $sco->launch = '';
            $sco->scormtype = '';
            $sco->sortorder = $sortorder;

            if ($ss = $DB->get_record('scorm_scoes', array('scorm' => $scorm->id,
                                                           'identifier' => $sco->identifier))) {
                $id = $ss->id;
                $sco->id = $id;
                $DB->update_record('scorm_scoes', $sco);
                unset($oldscoes[$id]);
            } else {
                $id = $DB->insert_record('scorm_scoes', $sco);
            }

            if ($launch == 0) {
                $launch = $id;
            }
            if (isset($course->elements)) {
                foreach ($course->elements as $element) {
                    unset($sco);
                    $sco = new stdClass();
                    $sco->identifier = $element->system_id;
                    $sco->scorm = $scorm->id;
                    $sco->organization = $course->id;
                    $sco->title = $element->title;

                    if (!isset($element->parent)) {
                        $sco->parent = '/';
                    } else if (strtolower($element->parent) == 'root') {
                        $sco->parent = $course->id;
                    } else {
                        $sco->parent = $element->parent;
                    }
                    $sco->launch = '';
                    $sco->scormtype = '';
                    $sco->previous = 0;
                    $sco->next = 0;
                    $id = null;
                                        if (isset($element->file_name)) {
                        $sco->launch = $element->file_name;
                        $sco->scormtype = 'sco';
                    }
                    if ($oldscoid = scorm_array_search('identifier', $sco->identifier, $oldscoes)) {
                        $sco->id = $oldscoid;
                        $DB->update_record('scorm_scoes', $sco);
                        $id = $oldscoid;
                        $DB->delete_records('scorm_scoes_data', array('scoid' => $oldscoid));
                        unset($oldscoes[$oldscoid]);
                    } else {
                        $id = $DB->insert_record('scorm_scoes', $sco);
                    }
                    if (!empty($id)) {
                        $scodata = new stdClass();
                        $scodata->scoid = $id;
                        if (isset($element->web_launch)) {
                            $scodata->name = 'parameters';
                            $scodata->value = $element->web_launch;
                            $dataid = $DB->insert_record('scorm_scoes_data', $scodata);
                        }
                        if (isset($element->prerequisites)) {
                            $scodata->name = 'prerequisites';
                            $scodata->value = $element->prerequisites;
                            $dataid = $DB->insert_record('scorm_scoes_data', $scodata);
                        }
                        if (isset($element->max_time_allowed)) {
                            $scodata->name = 'max_time_allowed';
                            $scodata->value = $element->max_time_allowed;
                            $dataid = $DB->insert_record('scorm_scoes_data', $scodata);
                        }
                        if (isset($element->time_limit_action)) {
                            $scodata->name = 'time_limit_action';
                            $scodata->value = $element->time_limit_action;
                            $dataid = $DB->insert_record('scorm_scoes_data', $scodata);
                        }
                        if (isset($element->mastery_score)) {
                            $scodata->name = 'mastery_score';
                            $scodata->value = $element->mastery_score;
                            $dataid = $DB->insert_record('scorm_scoes_data', $scodata);
                        }
                        if (isset($element->core_vendor)) {
                            $scodata->name = 'datafromlms';
                            $scodata->value = preg_replace('/<cr>/i', "\r\n", $element->core_vendor);
                            $dataid = $DB->insert_record('scorm_scoes_data', $scodata);
                        }
                    }
                    if ($launch == 0) {
                        $launch = $id;
                    }
                }
            }
        }
    }
    if (!empty($oldscoes)) {
        foreach ($oldscoes as $oldsco) {
            $DB->delete_records('scorm_scoes', array('id' => $oldsco->id));
            $DB->delete_records('scorm_scoes_track', array('scoid' => $oldsco->id));
        }
    }

        $sqlselect = 'scorm = ? AND '.$DB->sql_isnotempty('scorm_scoes', 'launch', false, true);
        $scoes = $DB->get_records_select('scorm_scoes', $sqlselect, array($scorm->id), 'sortorder', 'id', 0, 1);
    if (!empty($scoes)) {
        $sco = reset($scoes);         $scorm->launch = $sco->id;
    } else {
        $scorm->launch = $launch;
    }

    $scorm->version = 'AICC';

    return true;
}


function scorm_aicc_get_hacp_session($scormid) {
    global $USER, $DB, $SESSION;
    $cfgscorm = get_config('scorm');
    if (empty($cfgscorm->allowaicchacp)) {
        return false;
    }
    $now = time();

    $hacpsession = $SESSION->scorm;
    $hacpsession->scormid = $scormid;
    $hacpsession->hacpsession = random_string(20);
    $hacpsession->userid      = $USER->id;
    $hacpsession->timecreated = $now;
    $hacpsession->timemodified = $now;
    $DB->insert_record('scorm_aicc_session', $hacpsession);

    return $hacpsession->hacpsession;
}


function scorm_aicc_confirm_hacp_session($hacpsession) {
    global $DB;
    $cfgscorm = get_config('scorm');
    if (empty($cfgscorm->allowaicchacp)) {
        return false;
    }
    $time = time() - ($cfgscorm->aicchacptimeout * 60);
    $sql = "hacpsession = ? AND timemodified > ?";
    $hacpsession = $DB->get_record_select('scorm_aicc_session', $sql, array($hacpsession, $time));
    if (!empty($hacpsession)) {         $hacpsession->timemodified = time();
        $DB->update_record('scorm_aicc_session', $hacpsession);
    }
    return $hacpsession;
}


function scorm_aicc_generate_simple_sco($scorm) {
    global $DB;
        $scos = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id), 'id');
    if (!empty($scos)) {
        $sco = array_shift($scos);
    } else {
        $sco = new stdClass();
    }
        foreach ($scos as $oldsco) {
        $DB->delete_records('scorm_scoes', array('id' => $oldsco->id));
        $DB->delete_records('scorm_scoes_track', array('scoid' => $oldsco->id));
    }

    $sco->identifier = 'A1';
    $sco->scorm = $scorm->id;
    $sco->organization = '';
    $sco->title = $scorm->name;
    $sco->parent = '/';
        if (preg_match('/\?/', $scorm->reference)) {
        $sco->launch = $scorm->reference.'&CMI=HACP';
    } else {
        $sco->launch = $scorm->reference.'?CMI=HACP';
    }
    $sco->scormtype = 'sco';
    if (isset($sco->id)) {
        $DB->update_record('scorm_scoes', $sco);
        $id = $sco->id;
    } else {
        $id = $DB->insert_record('scorm_scoes', $sco);
    }
    return $id;
}


function get_scorm_default (&$userdata, $scorm, $scoid, $attempt, $mode) {
    global $USER;
    $aiccuserid = get_config('scorm', 'aiccuserid');
    if (!empty($aiccuserid)) {
        $userdata->student_id = $USER->id;
    } else {
        $userdata->student_id = $USER->username;
    }
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

    $userdata->mode = 'normal';
    if (!empty($mode)) {
        $userdata->mode = $mode;
    }
    if ($userdata->mode == 'normal') {
        $userdata->credit = 'credit';
    } else {
        $userdata->credit = 'no-credit';
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

    $def = array();
    $def['cmi.core.student_id'] = $userdata->student_id;
    $def['cmi.core.student_name'] = $userdata->student_name;
    $def['cmi.core.credit'] = $userdata->credit;
    $def['cmi.core.entry'] = $userdata->entry;
    $def['cmi.launch_data'] = scorm_isset($userdata, 'datafromlms');
    $def['cmi.core.lesson_mode'] = $userdata->mode;
    $def['cmi.student_data.attempt_number'] = scorm_isset($userdata, 'cmi.student_data.attempt_number');
    $def['cmi.student_data.mastery_score'] = scorm_isset($userdata, 'mastery_score');
    $def['cmi.student_data.max_time_allowed'] = scorm_isset($userdata, 'max_time_allowed');
    $def['cmi.student_data.time_limit_action'] = scorm_isset($userdata, 'time_limit_action');
    $def['cmi.student_data.tries_during_lesson'] = scorm_isset($userdata, 'cmi.student_data.tries_during_lesson');

    $def['cmi.core.lesson_location'] = scorm_isset($userdata, 'cmi.core.lesson_location');
    $def['cmi.core.lesson_status'] = scorm_isset($userdata, 'cmi.core.lesson_status');
    $def['cmi.core.exit'] = scorm_isset($userdata, 'cmi.core.exit');
    $def['cmi.core.score.raw'] = scorm_isset($userdata, 'cmi.core.score.raw');
    $def['cmi.core.score.max'] = scorm_isset($userdata, 'cmi.core.score.max');
    $def['cmi.core.score.min'] = scorm_isset($userdata, 'cmi.core.score.min');
    $def['cmi.core.total_time'] = scorm_isset($userdata, 'cmi.core.total_time', '00:00:00');
    $def['cmi.suspend_data'] = scorm_isset($userdata, 'cmi.suspend_data');
    $def['cmi.comments'] = scorm_isset($userdata, 'cmi.comments');

    return $def;
}
