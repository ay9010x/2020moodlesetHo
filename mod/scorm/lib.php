<?php




define('SCORM_TYPE_LOCAL', 'local');

define('SCORM_TYPE_LOCALSYNC', 'localsync');

define('SCORM_TYPE_EXTERNAL', 'external');

define('SCORM_TYPE_AICCURL', 'aiccurl');

define('SCORM_TOC_SIDE', 0);
define('SCORM_TOC_HIDDEN', 1);
define('SCORM_TOC_POPUP', 2);
define('SCORM_TOC_DISABLED', 3);

define('SCORM_NAV_DISABLED', 0);
define('SCORM_NAV_UNDER_CONTENT', 1);
define('SCORM_NAV_FLOATING', 2);

define('SCORM_12', 1);
define('SCORM_13', 2);
define('SCORM_AICC', 3);

define('SCORM_DISPLAY_ATTEMPTSTATUS_NO', 0);
define('SCORM_DISPLAY_ATTEMPTSTATUS_ALL', 1);
define('SCORM_DISPLAY_ATTEMPTSTATUS_MY', 2);
define('SCORM_DISPLAY_ATTEMPTSTATUS_ENTRY', 3);


function scorm_status_options($withstrings = false) {
        $options = array(
        2 => 'passed',
        4 => 'completed'
    );

    if ($withstrings) {
        foreach ($options as $key => $value) {
            $options[$key] = get_string('completionstatus_'.$value, 'scorm');
        }
    }

    return $options;
}



function scorm_add_instance($scorm, $mform=null) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/scorm/locallib.php');

    if (empty($scorm->timeopen)) {
        $scorm->timeopen = 0;
    }
    if (empty($scorm->timeclose)) {
        $scorm->timeclose = 0;
    }
    $cmid       = $scorm->coursemodule;
    $cmidnumber = $scorm->cmidnumber;
    $courseid   = $scorm->course;

    $context = context_module::instance($cmid);

    $scorm = scorm_option2text($scorm);
    $scorm->width  = (int)str_replace('%', '', $scorm->width);
    $scorm->height = (int)str_replace('%', '', $scorm->height);

    if (!isset($scorm->whatgrade)) {
        $scorm->whatgrade = 0;
    }

    $id = $DB->insert_record('scorm', $scorm);

        $DB->set_field('course_modules', 'instance', $id, array('id' => $cmid));

        $record = $DB->get_record('scorm', array('id' => $id));

        if ($record->scormtype === SCORM_TYPE_LOCAL) {
        if (!empty($scorm->packagefile)) {
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_scorm', 'package');
            file_save_draft_area_files($scorm->packagefile, $context->id, 'mod_scorm', 'package',
                0, array('subdirs' => 0, 'maxfiles' => 1));
                        $files = $fs->get_area_files($context->id, 'mod_scorm', 'package', 0, '', false);
            $file = reset($files);
            $filename = $file->get_filename();
            if ($filename !== false) {
                $record->reference = $filename;
            }
        }

    } else if ($record->scormtype === SCORM_TYPE_LOCALSYNC) {
        $record->reference = $scorm->packageurl;
    } else if ($record->scormtype === SCORM_TYPE_EXTERNAL) {
        $record->reference = $scorm->packageurl;
    } else if ($record->scormtype === SCORM_TYPE_AICCURL) {
        $record->reference = $scorm->packageurl;
        $record->hidetoc = SCORM_TOC_DISABLED;     } else {
        return false;
    }

        $DB->update_record('scorm', $record);

        $record->course     = $courseid;
    $record->cmidnumber = $cmidnumber;
    $record->cmid       = $cmid;

    scorm_parse($record, true);

    scorm_grade_item_update($record);

    return $record->id;
}


function scorm_update_instance($scorm, $mform=null) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/scorm/locallib.php');

    if (empty($scorm->timeopen)) {
        $scorm->timeopen = 0;
    }
    if (empty($scorm->timeclose)) {
        $scorm->timeclose = 0;
    }

    $cmid       = $scorm->coursemodule;
    $cmidnumber = $scorm->cmidnumber;
    $courseid   = $scorm->course;

    $scorm->id = $scorm->instance;

    $context = context_module::instance($cmid);

    if ($scorm->scormtype === SCORM_TYPE_LOCAL) {
        if (!empty($scorm->packagefile)) {
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_scorm', 'package');
            file_save_draft_area_files($scorm->packagefile, $context->id, 'mod_scorm', 'package',
                0, array('subdirs' => 0, 'maxfiles' => 1));
                        $files = $fs->get_area_files($context->id, 'mod_scorm', 'package', 0, '', false);
            $file = reset($files);
            $filename = $file->get_filename();
            if ($filename !== false) {
                $scorm->reference = $filename;
            }
        }

    } else if ($scorm->scormtype === SCORM_TYPE_LOCALSYNC) {
        $scorm->reference = $scorm->packageurl;
    } else if ($scorm->scormtype === SCORM_TYPE_EXTERNAL) {
        $scorm->reference = $scorm->packageurl;
    } else if ($scorm->scormtype === SCORM_TYPE_AICCURL) {
        $scorm->reference = $scorm->packageurl;
        $scorm->hidetoc = SCORM_TOC_DISABLED;     } else {
        return false;
    }

    $scorm = scorm_option2text($scorm);
    $scorm->width        = (int)str_replace('%', '', $scorm->width);
    $scorm->height       = (int)str_replace('%', '', $scorm->height);
    $scorm->timemodified = time();

    if (!isset($scorm->whatgrade)) {
        $scorm->whatgrade = 0;
    }

    $DB->update_record('scorm', $scorm);

    $scorm = $DB->get_record('scorm', array('id' => $scorm->id));

        $scorm->course   = $courseid;
    $scorm->idnumber = $cmidnumber;
    $scorm->cmid     = $cmid;

    scorm_parse($scorm, (bool)$scorm->updatefreq);

    scorm_grade_item_update($scorm);
    scorm_update_grades($scorm);

    return true;
}


function scorm_delete_instance($id) {
    global $CFG, $DB;

    if (! $scorm = $DB->get_record('scorm', array('id' => $id))) {
        return false;
    }

    $result = true;

        if (! $DB->delete_records('scorm_scoes_track', array('scormid' => $scorm->id))) {
        $result = false;
    }
    if ($scoes = $DB->get_records('scorm_scoes', array('scorm' => $scorm->id))) {
        foreach ($scoes as $sco) {
            if (! $DB->delete_records('scorm_scoes_data', array('scoid' => $sco->id))) {
                $result = false;
            }
        }
        $DB->delete_records('scorm_scoes', array('scorm' => $scorm->id));
    }
    if (! $DB->delete_records('scorm', array('id' => $scorm->id))) {
        $result = false;
    }

    

    scorm_grade_item_delete($scorm);

    return $result;
}


function scorm_user_outline($course, $user, $mod, $scorm) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/scorm/locallib.php');

    require_once("$CFG->libdir/gradelib.php");
    $grades = grade_get_grades($course->id, 'mod', 'scorm', $scorm->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        $result = new stdClass();
        $result->info = get_string('grade') . ': '. $grade->str_long_grade;

                                if ($grade->usermodified == $user->id || empty($grade->datesubmitted)) {
            $result->time = $grade->dategraded;
        } else {
            $result->time = $grade->datesubmitted;
        }

        return $result;
    }
    return null;
}


function scorm_user_complete($course, $user, $mod, $scorm) {
    global $CFG, $DB, $OUTPUT;
    require_once("$CFG->libdir/gradelib.php");

    $liststyle = 'structlist';
    $now = time();
    $firstmodify = $now;
    $lastmodify = 0;
    $sometoreport = false;
    $report = '';

        require_once($CFG->dirroot.'/mod/scorm/locallib.php');
    $timetracks = scorm_get_sco_runtime($scorm->id, false, $user->id);
    $firstmodify = $timetracks->start;
    $lastmodify = $timetracks->finish;

    $grades = grade_get_grades($course->id, 'mod', 'scorm', $scorm->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        echo $OUTPUT->container(get_string('grade').': '.$grade->str_long_grade);
        if ($grade->str_feedback) {
            echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
        }
    }

    if ($orgs = $DB->get_records_select('scorm_scoes', 'scorm = ? AND '.
                                         $DB->sql_isempty('scorm_scoes', 'launch', false, true).' AND '.
                                         $DB->sql_isempty('scorm_scoes', 'organization', false, false),
                                         array($scorm->id), 'sortorder, id', 'id, identifier, title')) {
        if (count($orgs) <= 1) {
            unset($orgs);
            $orgs = array();
            $org = new stdClass();
            $org->identifier = '';
            $orgs[] = $org;
        }
        $report .= html_writer::start_div('mod-scorm');
        foreach ($orgs as $org) {
            $conditions = array();
            $currentorg = '';
            if (!empty($org->identifier)) {
                $report .= html_writer::div($org->title, 'orgtitle');
                $currentorg = $org->identifier;
                $conditions['organization'] = $currentorg;
            }
            $report .= html_writer::start_tag('ul', array('id' => '0', 'class' => $liststyle));
                $conditions['scorm'] = $scorm->id;
            if ($scoes = $DB->get_records('scorm_scoes', $conditions, "sortorder, id")) {
                                $scoes = array_values($scoes);
                $level = 0;
                $sublist = 1;
                $parents[$level] = '/';
                foreach ($scoes as $pos => $sco) {
                    if ($parents[$level] != $sco->parent) {
                        if ($level > 0 && $parents[$level - 1] == $sco->parent) {
                            $report .= html_writer::end_tag('ul').html_writer::end_tag('li');
                            $level--;
                        } else {
                            $i = $level;
                            $closelist = '';
                            while (($i > 0) && ($parents[$level] != $sco->parent)) {
                                $closelist .= html_writer::end_tag('ul').html_writer::end_tag('li');
                                $i--;
                            }
                            if (($i == 0) && ($sco->parent != $currentorg)) {
                                $report .= html_writer::start_tag('li');
                                $report .= html_writer::start_tag('ul', array('id' => $sublist, 'class' => $liststyle));
                                $level++;
                            } else {
                                $report .= $closelist;
                                $level = $i;
                            }
                            $parents[$level] = $sco->parent;
                        }
                    }
                    $report .= html_writer::start_tag('li');
                    if (isset($scoes[$pos + 1])) {
                        $nextsco = $scoes[$pos + 1];
                    } else {
                        $nextsco = false;
                    }
                    if (($nextsco !== false) && ($sco->parent != $nextsco->parent) &&
                            (($level == 0) || (($level > 0) && ($nextsco->parent == $sco->identifier)))) {
                        $sublist++;
                    } else {
                        $report .= $OUTPUT->spacer(array("height" => "12", "width" => "13"));
                    }

                    if ($sco->launch) {
                        $score = '';
                        $totaltime = '';
                        if ($usertrack = scorm_get_tracks($sco->id, $user->id)) {
                            if ($usertrack->status == '') {
                                $usertrack->status = 'notattempted';
                            }
                            $strstatus = get_string($usertrack->status, 'scorm');
                            $report .= html_writer::img($OUTPUT->pix_url($usertrack->status, 'scorm'),
                                                        $strstatus, array('title' => $strstatus));
                        } else {
                            if ($sco->scormtype == 'sco') {
                                $report .= html_writer::img($OUTPUT->pix_url('notattempted', 'scorm'),
                                                            get_string('notattempted', 'scorm'),
                                                            array('title' => get_string('notattempted', 'scorm')));
                            } else {
                                $report .= html_writer::img($OUTPUT->pix_url('asset', 'scorm'), get_string('asset', 'scorm'),
                                                            array('title' => get_string('asset', 'scorm')));
                            }
                        }
                        $report .= "&nbsp;$sco->title $score$totaltime".html_writer::end_tag('li');
                        if ($usertrack !== false) {
                            $sometoreport = true;
                            $report .= html_writer::start_tag('li').html_writer::start_tag('ul', array('class' => $liststyle));
                            foreach ($usertrack as $element => $value) {
                                if (substr($element, 0, 3) == 'cmi') {
                                    $report .= html_writer::tag('li', $element.' => '.s($value));
                                }
                            }
                            $report .= html_writer::end_tag('ul').html_writer::end_tag('li');
                        }
                    } else {
                        $report .= "&nbsp;$sco->title".html_writer::end_tag('li');
                    }
                }
                for ($i = 0; $i < $level; $i++) {
                    $report .= html_writer::end_tag('ul').html_writer::end_tag('li');
                }
            }
            $report .= html_writer::end_tag('ul').html_writer::empty_tag('br');
        }
        $report .= html_writer::end_div();
    }
    if ($sometoreport) {
        if ($firstmodify < $now) {
            $timeago = format_time($now - $firstmodify);
            echo get_string('firstaccess', 'scorm').': '.userdate($firstmodify).' ('.$timeago.")".html_writer::empty_tag('br');
        }
        if ($lastmodify > 0) {
            $timeago = format_time($now - $lastmodify);
            echo get_string('lastaccess', 'scorm').': '.userdate($lastmodify).' ('.$timeago.")".html_writer::empty_tag('br');
        }
        echo get_string('report', 'scorm').":".html_writer::empty_tag('br');
        echo $report;
    } else {
        print_string('noactivity', 'scorm');
    }

    return true;
}


function scorm_cron () {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/scorm/locallib.php');

    $sitetimezone = core_date::get_server_timezone();
    
    if (!isset($CFG->scorm_updatetimelast)) {            set_config('scorm_updatetimelast', 0);
    }

    $timenow = time();
    $updatetime = usergetmidnight($timenow, $sitetimezone);

    if ($CFG->scorm_updatetimelast < $updatetime and $timenow > $updatetime) {

        set_config('scorm_updatetimelast', $timenow);

        mtrace('Updating scorm packages which require daily update');
        $scormsupdate = $DB->get_records('scorm', array('updatefreq' => SCORM_UPDATE_EVERYDAY));
        foreach ($scormsupdate as $scormupdate) {
            scorm_parse($scormupdate, true);
        }

                $cfgscorm = get_config('scorm');
        if (!empty($cfgscorm->allowaicchacp)) {
            $expiretime = time() - ($cfgscorm->aicchacpkeepsessiondata * 24 * 60 * 60);
            $DB->delete_records_select('scorm_aicc_session', 'timemodified < ?', array($expiretime));
        }
    }

    return true;
}


function scorm_get_user_grades($scorm, $userid=0) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/scorm/locallib.php');

    $grades = array();
    if (empty($userid)) {
        $scousers = $DB->get_records_select('scorm_scoes_track', "scormid=? GROUP BY userid",
                                            array($scorm->id), "", "userid,null");
        if ($scousers) {
            foreach ($scousers as $scouser) {
                $grades[$scouser->userid] = new stdClass();
                $grades[$scouser->userid]->id         = $scouser->userid;
                $grades[$scouser->userid]->userid     = $scouser->userid;
                $grades[$scouser->userid]->rawgrade = scorm_grade_user($scorm, $scouser->userid);
            }
        } else {
            return false;
        }

    } else {
        $preattempt = $DB->get_records_select('scorm_scoes_track', "scormid=? AND userid=? GROUP BY userid",
                                                array($scorm->id, $userid), "", "userid,null");
        if (!$preattempt) {
            return false;         }
        $grades[$userid] = new stdClass();
        $grades[$userid]->id         = $userid;
        $grades[$userid]->userid     = $userid;
        $grades[$userid]->rawgrade = scorm_grade_user($scorm, $userid);
    }

    return $grades;
}


function scorm_update_grades($scorm, $userid=0, $nullifnone=true) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->libdir.'/completionlib.php');

    if ($grades = scorm_get_user_grades($scorm, $userid)) {
        scorm_grade_item_update($scorm, $grades);
                scorm_set_completion($scorm, $userid, COMPLETION_COMPLETE, $grades);
    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        scorm_grade_item_update($scorm, $grade);
                scorm_set_completion($scorm, $userid, COMPLETION_INCOMPLETE);
    } else {
        scorm_grade_item_update($scorm);
    }
}


function scorm_grade_item_update($scorm, $grades=null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/scorm/locallib.php');
    if (!function_exists('grade_update')) {         require_once($CFG->libdir.'/gradelib.php');
    }

    $params = array('itemname' => $scorm->name);
    if (isset($scorm->cmidnumber)) {
        $params['idnumber'] = $scorm->cmidnumber;
    }

    if ($scorm->grademethod == GRADESCOES) {
        $maxgrade = $DB->count_records_select('scorm_scoes', 'scorm = ? AND '.
                                                $DB->sql_isnotempty('scorm_scoes', 'launch', false, true), array($scorm->id));
        if ($maxgrade) {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = $maxgrade;
            $params['grademin']  = 0;
        } else {
            $params['gradetype'] = GRADE_TYPE_NONE;
        }
    } else {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $scorm->maxgrade;
        $params['grademin']  = 0;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/scorm', $scorm->course, 'mod', 'scorm', $scorm->id, 0, $grades, $params);
}


function scorm_grade_item_delete($scorm) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/scorm', $scorm->course, 'mod', 'scorm', $scorm->id, 0, null, array('deleted' => 1));
}


function scorm_get_view_actions() {
    return array('pre-view', 'view', 'view all', 'report');
}


function scorm_get_post_actions() {
    return array();
}


function scorm_option2text($scorm) {
    $scormpopoupoptions = scorm_get_popup_options_array();

    if (isset($scorm->popup)) {
        if ($scorm->popup == 1) {
            $optionlist = array();
            foreach ($scormpopoupoptions as $name => $option) {
                if (isset($scorm->$name)) {
                    $optionlist[] = $name.'='.$scorm->$name;
                } else {
                    $optionlist[] = $name.'=0';
                }
            }
            $scorm->options = implode(',', $optionlist);
        } else {
            $scorm->options = '';
        }
    } else {
        $scorm->popup = 0;
        $scorm->options = '';
    }
    return $scorm;
}


function scorm_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'scormheader', get_string('modulenameplural', 'scorm'));
    $mform->addElement('advcheckbox', 'reset_scorm', get_string('deleteallattempts', 'scorm'));
}


function scorm_reset_course_form_defaults($course) {
    return array('reset_scorm' => 1);
}


function scorm_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT s.*, cm.idnumber as cmidnumber, s.course as courseid
              FROM {scorm} s, {course_modules} cm, {modules} m
             WHERE m.name='scorm' AND m.id=cm.module AND cm.instance=s.id AND s.course=?";

    if ($scorms = $DB->get_records_sql($sql, array($courseid))) {
        foreach ($scorms as $scorm) {
            scorm_grade_item_update($scorm, 'reset');
        }
    }
}


function scorm_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'scorm');
    $status = array();

    if (!empty($data->reset_scorm)) {
        $scormssql = "SELECT s.id
                         FROM {scorm} s
                        WHERE s.course=?";

        $DB->delete_records_select('scorm_scoes_track', "scormid IN ($scormssql)", array($data->courseid));

                if (empty($data->reset_gradebook_grades)) {
            scorm_reset_gradebook($data->courseid);
        }

        $status[] = array('component' => $componentstr, 'item' => get_string('deleteallattempts', 'scorm'), 'error' => false);
    }

    
    return $status;
}


function scorm_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function scorm_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('areacontent', 'scorm');
    $areas['package'] = get_string('areapackage', 'scorm');
    return $areas;
}


function scorm_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        return null;
    }

    
    $fs = get_file_storage();

    if ($filearea === 'content') {

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_scorm', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_scorm', 'content', 0);
            } else {
                                return null;
            }
        }
        require_once("$CFG->dirroot/mod/scorm/locallib.php");
        return new scorm_package_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, false, false);

    } else if ($filearea === 'package') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_scorm', 'package', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_scorm', 'package', 0);
            } else {
                                return null;
            }
        }
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, false, false);
    }

    
    return false;
}


function scorm_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    $canmanageactivity = has_capability('moodle/course:manageactivities', $context);
    $lifetime = null;

        if (!$canmanageactivity) {
        require_once($CFG->dirroot.'/mod/scorm/locallib.php');

        $scorm = $DB->get_record('scorm', array('id' => $cm->instance), 'id, timeopen, timeclose', MUST_EXIST);
        list($available, $warnings) = scorm_get_availability_status($scorm);
        if (!$available) {
            return false;
        }
    }

    if ($filearea === 'content') {
        $revision = (int)array_shift($args);         $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_scorm/content/0/$relativepath";
        
    } else if ($filearea === 'package') {
                $protectpackagedownloads = get_config('scorm', 'protectpackagedownloads');
        if ($protectpackagedownloads and !$canmanageactivity) {
            return false;
        }
        $revision = (int)array_shift($args);         $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_scorm/package/0/$relativepath";
        $lifetime = 0; 
    } else if ($filearea === 'imsmanifest') {         $revision = (int)array_shift($args);         $relativepath = implode('/', $args);

                $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_scorm', 'package', 0, '', false);
        $file = reset($files);

                $packagefilename = $file->get_filename();
        if (strtolower($packagefilename) !== 'imsmanifest.xml') {
            return false;
        }

        $file->send_relative_file($relativepath);
    } else {
        return false;
    }

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        if ($filearea === 'content') {             send_header_404();
            die;
        }
        return false;
    }

        send_stored_file($file, $lifetime, 0, false, $options);
}


function scorm_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


function scorm_debug_log_filename($type, $scoid) {
    global $CFG, $USER;

    $logpath = $CFG->tempdir.'/scormlogs';
    $logfile = $logpath.'/'.$type.'debug_'.$USER->id.'_'.$scoid.'.log';
    return $logfile;
}


function scorm_debug_log_write($type, $text, $scoid) {
    global $CFG;

    $debugenablelog = get_config('scorm', 'allowapidebug');
    if (!$debugenablelog || empty($text)) {
        return;
    }
    if (make_temp_directory('scormlogs/')) {
        $logfile = scorm_debug_log_filename($type, $scoid);
        @file_put_contents($logfile, date('Y/m/d H:i:s O')." DEBUG $text\r\n", FILE_APPEND);
        @chmod($logfile, $CFG->filepermissions);
    }
}


function scorm_debug_log_remove($type, $scoid) {

    $debugenablelog = get_config('scorm', 'allowapidebug');
    $logfile = scorm_debug_log_filename($type, $scoid);
    if (!$debugenablelog || !file_exists($logfile)) {
        return false;
    }

    return @unlink($logfile);
}


function scorm_print_overview($courses, &$htmlarray) {
    global $USER, $CFG;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$scorms = get_all_instances_in_courses('scorm', $courses)) {
        return;
    }

    $strscorm   = get_string('modulename', 'scorm');
    $strduedate = get_string('duedate', 'scorm');

    foreach ($scorms as $scorm) {
        $time = time();
        $showattemptstatus = false;
        if ($scorm->timeopen) {
            $isopen = ($scorm->timeopen <= $time && $time <= $scorm->timeclose);
        }
        if ($scorm->displayattemptstatus == SCORM_DISPLAY_ATTEMPTSTATUS_ALL ||
                $scorm->displayattemptstatus == SCORM_DISPLAY_ATTEMPTSTATUS_MY) {
            $showattemptstatus = true;
        }
        if ($showattemptstatus || !empty($isopen) || !empty($scorm->timeclose)) {
            $str = html_writer::start_div('scorm overview').html_writer::div($strscorm. ': '.
                    html_writer::link($CFG->wwwroot.'/mod/scorm/view.php?id='.$scorm->coursemodule, $scorm->name,
                                        array('title' => $strscorm, 'class' => $scorm->visible ? '' : 'dimmed')), 'name');
            if ($scorm->timeclose) {
                $str .= html_writer::div($strduedate.': '.userdate($scorm->timeclose), 'info');
            }
            if ($showattemptstatus) {
                require_once($CFG->dirroot.'/mod/scorm/locallib.php');
                $str .= html_writer::div(scorm_get_attempt_status($USER, $scorm), 'details');
            }
            $str .= html_writer::end_div();
            if (empty($htmlarray[$scorm->course]['scorm'])) {
                $htmlarray[$scorm->course]['scorm'] = $str;
            } else {
                $htmlarray[$scorm->course]['scorm'] .= $str;
            }
        }
    }
}


function scorm_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-scorm-*' => get_string('page-mod-scorm-x', 'scorm'));
    return $modulepagetype;
}


function scorm_version_check($scormversion, $version='') {
    $scormversion = trim(strtolower($scormversion));
    if (empty($version) || $version == SCORM_12) {
        if ($scormversion == 'scorm_12' || $scormversion == 'scorm_1.2') {
            return SCORM_12;
        }
        if (!empty($version)) {
            return false;
        }
    }
    if (empty($version) || $version == SCORM_13) {
        if ($scormversion == 'scorm_13' || $scormversion == 'scorm_1.3') {
            return SCORM_13;
        }
        if (!empty($version)) {
            return false;
        }
    }
    if (empty($version) || $version == SCORM_AICC) {
        if (strpos($scormversion, 'aicc')) {
            return SCORM_AICC;
        }
        if (!empty($version)) {
            return false;
        }
    }
    return false;
}


function scorm_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $result = $type;

        if (!$scorm = $DB->get_record('scorm', array('id' => $cm->instance))) {
        print_error('cannotfindscorm');
    }
            if ($scorm->completionstatusrequired !== null ||
        $scorm->completionscorerequired !== null) {
                $tracks = $DB->get_records_sql(
            "
            SELECT
                id,
                element,
                value
            FROM
                {scorm_scoes_track}
            WHERE
                scormid = ?
            AND userid = ?
            AND element IN
            (
                'cmi.core.lesson_status',
                'cmi.completion_status',
                'cmi.success_status',
                'cmi.core.score.raw',
                'cmi.score.raw'
            )
            ",
            array($scorm->id, $userid)
        );

        if (!$tracks) {
            return completion_info::aggregate_completion_states($type, $result, false);
        }
    }

        if ($scorm->completionstatusrequired !== null) {

                $statuses = array_flip(scorm_status_options());
        $nstatus = 0;

        foreach ($tracks as $track) {
            if (!in_array($track->element, array('cmi.core.lesson_status', 'cmi.completion_status', 'cmi.success_status'))) {
                continue;
            }

            if (array_key_exists($track->value, $statuses)) {
                $nstatus |= $statuses[$track->value];
            }
        }

        if ($scorm->completionstatusrequired & $nstatus) {
            return completion_info::aggregate_completion_states($type, $result, true);
        } else {
            return completion_info::aggregate_completion_states($type, $result, false);
        }

    }

        if ($scorm->completionscorerequired !== null) {
        $maxscore = -1;

        foreach ($tracks as $track) {
            if (!in_array($track->element, array('cmi.core.score.raw', 'cmi.score.raw'))) {
                continue;
            }

            if (strlen($track->value) && floatval($track->value) >= $maxscore) {
                $maxscore = floatval($track->value);
            }
        }

        if ($scorm->completionscorerequired <= $maxscore) {
            return completion_info::aggregate_completion_states($type, $result, true);
        } else {
            return completion_info::aggregate_completion_states($type, $result, false);
        }
    }

    return $result;
}


function scorm_dndupload_register() {
    return array('files' => array(
        array('extension' => 'zip', 'message' => get_string('dnduploadscorm', 'scorm'))
    ));
}


function scorm_dndupload_handle($uploadinfo) {

    $context = context_module::instance($uploadinfo->coursemodule);
    file_save_draft_area_files($uploadinfo->draftitemid, $context->id, 'mod_scorm', 'package', 0);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_scorm', 'package', 0, 'sortorder, itemid, filepath, filename', false);
    $file = reset($files);

        $errors = scorm_validate_package($file);
    if (!empty($errors)) {
        return false;
    }
        $scorm = get_config('scorm');
    $scorm->course = $uploadinfo->course->id;
    $scorm->coursemodule = $uploadinfo->coursemodule;
    $scorm->cmidnumber = '';
    $scorm->name = $uploadinfo->displayname;
    $scorm->scormtype = SCORM_TYPE_LOCAL;
    $scorm->reference = $file->get_filename();
    $scorm->intro = '';
    $scorm->width = $scorm->framewidth;
    $scorm->height = $scorm->frameheight;

    return scorm_add_instance($scorm, null);
}


function scorm_set_completion($scorm, $userid, $completionstate = COMPLETION_COMPLETE, $grades = array()) {
    $course = new stdClass();
    $course->id = $scorm->course;
    $completion = new completion_info($course);

        if (!$completion->is_enabled()) {
        return;
    }

    $cm = get_coursemodule_from_instance('scorm', $scorm->id, $scorm->course);
    if (empty($cm) || !$completion->is_enabled($cm)) {
            return;
    }

    if (empty($userid)) {         foreach ($grades as $grade) {
            $completion->update_state($cm, $completionstate, $grade->userid);
        }
    } else {
        $completion->update_state($cm, $completionstate, $userid);
    }
}


function scorm_validate_package($file) {
    $packer = get_file_packer('application/zip');
    $errors = array();
    if ($file->is_external_file()) {         $file->import_external_file_contents();
    }
    $filelist = $file->list_files($packer);

    if (!is_array($filelist)) {
        $errors['packagefile'] = get_string('badarchive', 'scorm');
    } else {
        $aiccfound = false;
        $badmanifestpresent = false;
        foreach ($filelist as $info) {
            if ($info->pathname == 'imsmanifest.xml') {
                return array();
            } else if (strpos($info->pathname, 'imsmanifest.xml') !== false) {
                                $badmanifestpresent = true;
            }
            if (preg_match('/\.cst$/', $info->pathname)) {
                return array();
            }
        }
        if (!$aiccfound) {
            if ($badmanifestpresent) {
                $errors['packagefile'] = get_string('badimsmanifestlocation', 'scorm');
            } else {
                $errors['packagefile'] = get_string('nomanifest', 'scorm');
            }
        }
    }
    return $errors;
}


function scorm_check_mode($scorm, &$newattempt, &$attempt, $userid, &$mode) {
    global $DB;

    if (($mode == 'browse')) {
        if ($scorm->hidebrowse == 1) {
                        $mode = 'normal';
        } else {
                        return;
        }
    }
        $incomplete = true;
    $tracks = $DB->get_recordset('scorm_scoes_track', array('scormid' => $scorm->id, 'userid' => $userid,
        'attempt' => $attempt, 'element' => 'cmi.core.lesson_status'));
    foreach ($tracks as $track) {
        if (($track->value == 'completed') || ($track->value == 'passed') || ($track->value == 'failed')) {
            $incomplete = false;
        } else {
            $incomplete = true;
            break;         }
    }
    $tracks->close();

        if ($incomplete === true) {
                $newattempt = 'off';
    } else if (!empty($scorm->forcenewattempt)) {
                $newattempt = 'on';
    }

    if (($newattempt == 'on') && (($attempt < $scorm->maxattempt) || ($scorm->maxattempt == 0))) {
        $attempt++;
        $mode = 'normal';
    } else {         if ($incomplete === true) {
            $mode = 'normal';
        } else {
            $mode = 'review';
        }
    }
}


function scorm_view($scorm, $course, $cm, $context) {

        $params = array(
        'context' => $context,
        'objectid' => $scorm->id
    );

    $event = \mod_scorm\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('scorm', $scorm);
    $event->trigger();
}
