<?php




defined('MOODLE_INTERNAL') || die();


global $CHOICE_COLUMN_HEIGHT;
$CHOICE_COLUMN_HEIGHT = 300;


global $CHOICE_COLUMN_WIDTH;
$CHOICE_COLUMN_WIDTH = 300;

define('CHOICE_PUBLISH_ANONYMOUS', '0');
define('CHOICE_PUBLISH_NAMES',     '1');

define('CHOICE_SHOWRESULTS_NOT',          '0');
define('CHOICE_SHOWRESULTS_AFTER_ANSWER', '1');
define('CHOICE_SHOWRESULTS_AFTER_CLOSE',  '2');
define('CHOICE_SHOWRESULTS_ALWAYS',       '3');

define('CHOICE_DISPLAY_HORIZONTAL',  '0');
define('CHOICE_DISPLAY_VERTICAL',    '1');


global $CHOICE_PUBLISH;
$CHOICE_PUBLISH = array (CHOICE_PUBLISH_ANONYMOUS  => get_string('publishanonymous', 'choice'),
                         CHOICE_PUBLISH_NAMES      => get_string('publishnames', 'choice'));


global $CHOICE_SHOWRESULTS;
$CHOICE_SHOWRESULTS = array (CHOICE_SHOWRESULTS_NOT          => get_string('publishnot', 'choice'),
                         CHOICE_SHOWRESULTS_AFTER_ANSWER => get_string('publishafteranswer', 'choice'),
                         CHOICE_SHOWRESULTS_AFTER_CLOSE  => get_string('publishafterclose', 'choice'),
                         CHOICE_SHOWRESULTS_ALWAYS       => get_string('publishalways', 'choice'));


global $CHOICE_DISPLAY;
$CHOICE_DISPLAY = array (CHOICE_DISPLAY_HORIZONTAL   => get_string('displayhorizontal', 'choice'),
                         CHOICE_DISPLAY_VERTICAL     => get_string('displayvertical','choice'));



function choice_user_outline($course, $user, $mod, $choice) {
    global $DB;
    if ($answer = $DB->get_record('choice_answers', array('choiceid' => $choice->id, 'userid' => $user->id))) {
        $result = new stdClass();
        $result->info = "'".format_string(choice_get_option_text($choice, $answer->optionid))."'";
        $result->time = $answer->timemodified;
        return $result;
    }
    return NULL;
}


function choice_user_complete($course, $user, $mod, $choice) {
    global $DB;
    if ($answers = $DB->get_records('choice_answers', array("choiceid" => $choice->id, "userid" => $user->id))) {
        $info = [];
        foreach ($answers as $answer) {
            $info[] = "'" . format_string(choice_get_option_text($choice, $answer->optionid)) . "'";
        }
        core_collator::asort($info);
        echo get_string("answered", "choice") . ": ". join(', ', $info) . ". " .
                get_string("updated", '', userdate($answer->timemodified));
    } else {
        print_string("notanswered", "choice");
    }
}


function choice_add_instance($choice) {
    global $DB;

    $choice->timemodified = time();

    if (empty($choice->timerestrict)) {
        $choice->timeopen = 0;
        $choice->timeclose = 0;
    }

        $choice->id = $DB->insert_record("choice", $choice);
    foreach ($choice->option as $key => $value) {
        $value = trim($value);
        if (isset($value) && $value <> '') {
            $option = new stdClass();
            $option->text = $value;
            $option->choiceid = $choice->id;
            if (isset($choice->limit[$key])) {
                $option->maxanswers = $choice->limit[$key];
            }
            $option->timemodified = time();
            $DB->insert_record("choice_options", $option);
        }
    }

    return $choice->id;
}


function choice_update_instance($choice) {
    global $DB;

    $choice->id = $choice->instance;
    $choice->timemodified = time();


    if (empty($choice->timerestrict)) {
        $choice->timeopen = 0;
        $choice->timeclose = 0;
    }

        foreach ($choice->option as $key => $value) {
        $value = trim($value);
        $option = new stdClass();
        $option->text = $value;
        $option->choiceid = $choice->id;
        if (isset($choice->limit[$key])) {
            $option->maxanswers = $choice->limit[$key];
        }
        $option->timemodified = time();
        if (isset($choice->optionid[$key]) && !empty($choice->optionid[$key])){            $option->id=$choice->optionid[$key];
            if (isset($value) && $value <> '') {
                $DB->update_record("choice_options", $option);
            } else {
                                $DB->delete_records("choice_options", array("id" => $option->id));
                                $DB->delete_records("choice_answers", array("choiceid" => $choice->id, "optionid" => $option->id));
            }
        } else {
            if (isset($value) && $value <> '') {
                $DB->insert_record("choice_options", $option);
            }
        }
    }

    return $DB->update_record('choice', $choice);

}


function choice_prepare_options($choice, $user, $coursemodule, $allresponses) {
    global $DB;

    $cdisplay = array('options'=>array());

    $cdisplay['limitanswers'] = true;
    $context = context_module::instance($coursemodule->id);

    foreach ($choice->option as $optionid => $text) {
        if (isset($text)) {             $option = new stdClass;
            $option->attributes = new stdClass;
            $option->attributes->value = $optionid;
            $option->text = format_string($text);
            $option->maxanswers = $choice->maxanswers[$optionid];
            $option->displaylayout = $choice->display;

            if (isset($allresponses[$optionid])) {
                $option->countanswers = count($allresponses[$optionid]);
            } else {
                $option->countanswers = 0;
            }
            if ($DB->record_exists('choice_answers', array('choiceid' => $choice->id, 'userid' => $user->id, 'optionid' => $optionid))) {
                $option->attributes->checked = true;
            }
            if ( $choice->limitanswers && ($option->countanswers >= $option->maxanswers) && empty($option->attributes->checked)) {
                $option->attributes->disabled = true;
            }
            $cdisplay['options'][] = $option;
        }
    }

    $cdisplay['hascapability'] = is_enrolled($context, NULL, 'mod/choice:choose'); 
    if ($choice->allowupdate && $DB->record_exists('choice_answers', array('choiceid'=> $choice->id, 'userid'=> $user->id))) {
        $cdisplay['allowupdate'] = true;
    }

    if ($choice->showpreview && $choice->timeopen > time()) {
        $cdisplay['previewonly'] = true;
    }

    return $cdisplay;
}


function choice_user_submit_response($formanswer, $choice, $userid, $course, $cm) {
    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    $continueurl = new moodle_url('/mod/choice/view.php', array('id' => $cm->id));

    if (empty($formanswer)) {
        print_error('atleastoneoption', 'choice', $continueurl);
    }

    if (is_array($formanswer)) {
        if (!$choice->allowmultiple) {
            print_error('multiplenotallowederror', 'choice', $continueurl);
        }
        $formanswers = $formanswer;
    } else {
        $formanswers = array($formanswer);
    }

    $options = $DB->get_records('choice_options', array('choiceid' => $choice->id), '', 'id');
    foreach ($formanswers as $key => $val) {
        if (!isset($options[$val])) {
            print_error('cannotsubmit', 'choice', $continueurl);
        }
    }
            if ($choice->limitanswers) {
        $timeout = 10;
        $locktype = 'mod_choice_choice_user_submit_response';
                $resouce = 'choiceid:' . $choice->id;
        $lockfactory = \core\lock\lock_config::get_lock_factory($locktype);

                $choicelock = $lockfactory->get_lock($resouce, $timeout, MINSECS);
        if (!$choicelock) {
            print_error('cannotsubmit', 'choice', $continueurl);
        }
    }

    $current = $DB->get_records('choice_answers', array('choiceid' => $choice->id, 'userid' => $userid));
    $context = context_module::instance($cm->id);

    $choicesexceeded = false;
    $countanswers = array();
    foreach ($formanswers as $val) {
        $countanswers[$val] = 0;
    }
    if($choice->limitanswers) {
                if (groups_get_activity_groupmode($cm) > 0) {
            $currentgroup = groups_get_activity_group($cm);
        } else {
            $currentgroup = 0;
        }

        list ($insql, $params) = $DB->get_in_or_equal($formanswers, SQL_PARAMS_NAMED);

        if($currentgroup) {
                                    global $CFG;

            $params['groupid'] = $currentgroup;
            $sql = "SELECT ca.*
                      FROM {choice_answers} ca
                INNER JOIN {groups_members} gm ON ca.userid=gm.userid
                     WHERE optionid $insql
                       AND gm.groupid= :groupid";
        } else {
                        $sql = "SELECT ca.*
                      FROM {choice_answers} ca
                     WHERE optionid $insql";
        }

        $answers = $DB->get_records_sql($sql, $params);
        if ($answers) {
            foreach ($answers as $a) {                 if (is_enrolled($context, $a->userid, 'mod/choice:choose')) {
                    $countanswers[$a->optionid]++;
                }
            }
        }
        foreach ($countanswers as $opt => $count) {
            if ($count >= $choice->maxanswers[$opt]) {
                $choicesexceeded = true;
                break;
            }
        }
    }

        if (!($choice->limitanswers && $choicesexceeded)) {
        $answersnapshots = array();
        if ($current) {
                        $existingchoices = array();
            foreach ($current as $c) {
                if (in_array($c->optionid, $formanswers)) {
                    $existingchoices[] = $c->optionid;
                    $DB->set_field('choice_answers', 'timemodified', time(), array('id' => $c->id));
                    $answersnapshots[] = $c;
                } else {
                    $DB->delete_records('choice_answers', array('id' => $c->id));
                }
            }

                        foreach ($formanswers as $f) {
                if (!in_array($f, $existingchoices)) {
                    $newanswer = new stdClass();
                    $newanswer->optionid = $f;
                    $newanswer->choiceid = $choice->id;
                    $newanswer->userid = $userid;
                    $newanswer->timemodified = time();
                    $newanswer->id = $DB->insert_record("choice_answers", $newanswer);
                    $answersnapshots[] = $newanswer;
                }
            }

                        $answerupdated = true;
        } else {
                        foreach ($formanswers as $answer) {
                $newanswer = new stdClass();
                $newanswer->choiceid = $choice->id;
                $newanswer->userid = $userid;
                $newanswer->optionid = $answer;
                $newanswer->timemodified = time();
                $newanswer->id = $DB->insert_record("choice_answers", $newanswer);
                $answersnapshots[] = $newanswer;
            }

                        $completion = new completion_info($course);
            if ($completion->is_enabled($cm) && $choice->completionsubmit) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
            }

                        $answerupdated = false;
        }
    } else {
                $currentids = array_keys($current);

        if (array_diff($currentids, $formanswers) || array_diff($formanswers, $currentids) ) {
                        $choicelock->release();
            print_error('choicefull', 'choice', $continueurl);
        }
    }

        if (isset($choicelock)) {
        $choicelock->release();
    }

        if (isset($answerupdated)) {
        $eventdata = array();
        $eventdata['context'] = $context;
        $eventdata['objectid'] = $choice->id;
        $eventdata['userid'] = $userid;
        $eventdata['courseid'] = $course->id;
        $eventdata['other'] = array();
        $eventdata['other']['choiceid'] = $choice->id;

        if ($answerupdated) {
            $eventdata['other']['optionid'] = $formanswer;
            $event = \mod_choice\event\answer_updated::create($eventdata);
        } else {
            $eventdata['other']['optionid'] = $formanswers;
            $event = \mod_choice\event\answer_submitted::create($eventdata);
        }
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('course_modules', $cm);
        $event->add_record_snapshot('choice', $choice);
        foreach ($answersnapshots as $record) {
            $event->add_record_snapshot('choice_answers', $record);
        }
        $event->trigger();
    }
}


function choice_show_reportlink($user, $cm) {
    $userschosen = array();
    foreach($user as $optionid => $userlist) {
        if ($optionid) {
            $userschosen = array_merge($userschosen, array_keys($userlist));
        }
    }
    $responsecount = count(array_unique($userschosen));

    echo '<div class="reportlink">';
    echo "<a href=\"report.php?id=$cm->id\">".get_string("viewallresponses", "choice", $responsecount)."</a>";
    echo '</div>';
}


function prepare_choice_show_results($choice, $course, $cm, $allresponses) {
    global $OUTPUT;

    $display = clone($choice);
    $display->coursemoduleid = $cm->id;
    $display->courseid = $course->id;

        $display->options = array();
    $allusers = [];
    foreach ($choice->option as $optionid => $optiontext) {
        $display->options[$optionid] = new stdClass;
        $display->options[$optionid]->text = $optiontext;
        $display->options[$optionid]->maxanswer = $choice->maxanswers[$optionid];

        if (array_key_exists($optionid, $allresponses)) {
            $display->options[$optionid]->user = $allresponses[$optionid];
            $allusers = array_merge($allusers, array_keys($allresponses[$optionid]));
        }
    }
    unset($display->option);
    unset($display->maxanswers);

    $display->numberofuser = count(array_unique($allusers));
    $context = context_module::instance($cm->id);
    $display->viewresponsecapability = has_capability('mod/choice:readresponses', $context);
    $display->deleterepsonsecapability = has_capability('mod/choice:deleteresponses',$context);
    $display->fullnamecapability = has_capability('moodle/site:viewfullnames', $context);

    if (empty($allresponses)) {
        echo $OUTPUT->heading(get_string("nousersyet"), 3, null);
        return false;
    }

    return $display;
}


function choice_delete_responses($attemptids, $choice, $cm, $course) {
    global $DB, $CFG, $USER;
    require_once($CFG->libdir.'/completionlib.php');

    if(!is_array($attemptids) || empty($attemptids)) {
        return false;
    }

    foreach($attemptids as $num => $attemptid) {
        if(empty($attemptid)) {
            unset($attemptids[$num]);
        }
    }

    $context = context_module::instance($cm->id);
    $completion = new completion_info($course);
    foreach($attemptids as $attemptid) {
        if ($todelete = $DB->get_record('choice_answers', array('choiceid' => $choice->id, 'id' => $attemptid))) {
                        $eventdata = array();
            $eventdata['objectid'] = $todelete->id;
            $eventdata['context'] = $context;
            $eventdata['userid'] = $USER->id;
            $eventdata['courseid'] = $course->id;
            $eventdata['relateduserid'] = $todelete->userid;
            $eventdata['other'] = array();
            $eventdata['other']['choiceid'] = $choice->id;
            $eventdata['other']['optionid'] = $todelete->optionid;
            $event = \mod_choice\event\answer_deleted::create($eventdata);
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('course_modules', $cm);
            $event->add_record_snapshot('choice', $choice);
            $event->add_record_snapshot('choice_answers', $todelete);
            $event->trigger();

            $DB->delete_records('choice_answers', array('choiceid' => $choice->id, 'id' => $attemptid));
        }
    }

        if ($completion->is_enabled($cm) && $choice->completionsubmit) {
        $completion->update_state($cm, COMPLETION_INCOMPLETE);
    }

    return true;
}



function choice_delete_instance($id) {
    global $DB;

    if (! $choice = $DB->get_record("choice", array("id"=>"$id"))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("choice_answers", array("choiceid"=>"$choice->id"))) {
        $result = false;
    }

    if (! $DB->delete_records("choice_options", array("choiceid"=>"$choice->id"))) {
        $result = false;
    }

    if (! $DB->delete_records("choice", array("id"=>"$choice->id"))) {
        $result = false;
    }

    return $result;
}


function choice_get_option_text($choice, $id) {
    global $DB;

    if ($result = $DB->get_record("choice_options", array("id" => $id))) {
        return $result->text;
    } else {
        return get_string("notanswered", "choice");
    }
}


function choice_get_choice($choiceid) {
    global $DB;

    if ($choice = $DB->get_record("choice", array("id" => $choiceid))) {
        if ($options = $DB->get_records("choice_options", array("choiceid" => $choiceid), "id")) {
            foreach ($options as $option) {
                $choice->option[$option->id] = $option->text;
                $choice->maxanswers[$option->id] = $option->maxanswers;
            }
            return $choice;
        }
    }
    return false;
}


function choice_get_view_actions() {
    return array('view','view all','report');
}


function choice_get_post_actions() {
    return array('choose','choose again');
}



function choice_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'choiceheader', get_string('modulenameplural', 'choice'));
    $mform->addElement('advcheckbox', 'reset_choice', get_string('removeresponses','choice'));
}


function choice_reset_course_form_defaults($course) {
    return array('reset_choice'=>1);
}


function choice_reset_userdata($data) {
    global $CFG, $DB;

    $componentstr = get_string('modulenameplural', 'choice');
    $status = array();

    if (!empty($data->reset_choice)) {
        $choicessql = "SELECT ch.id
                       FROM {choice} ch
                       WHERE ch.course=?";

        $DB->delete_records_select('choice_answers', "choiceid IN ($choicessql)", array($data->courseid));
        $status[] = array('component'=>$componentstr, 'item'=>get_string('removeresponses', 'choice'), 'error'=>false);
    }

        if ($data->timeshift) {
        shift_course_mod_dates('choice', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('datechanged'), 'error'=>false);
    }

    return $status;
}


function choice_get_response_data($choice, $cm, $groupmode, $onlyactive) {
    global $CFG, $USER, $DB;

    $context = context_module::instance($cm->id);

    if ($groupmode > 0) {
        $currentgroup = groups_get_activity_group($cm);
    } else {
        $currentgroup = 0;
    }

    $allresponses = array();

    $allresponses[0] = get_enrolled_users($context, 'mod/choice:choose', $currentgroup,
            user_picture::fields('u', array('idnumber')), null, 0, 0, $onlyactive);

    $rawresponses = $DB->get_records('choice_answers', array('choiceid' => $choice->id));


    if ($rawresponses) {
        $answeredusers = array();
        foreach ($rawresponses as $response) {
            if (isset($allresponses[0][$response->userid])) {                   $allresponses[0][$response->userid]->timemodified = $response->timemodified;
                $allresponses[$response->optionid][$response->userid] = clone($allresponses[0][$response->userid]);
                $allresponses[$response->optionid][$response->userid]->answerid = $response->id;
                $answeredusers[] = $response->userid;
            }
        }
        foreach ($answeredusers as $answereduser) {
            unset($allresponses[0][$answereduser]);
        }
    }
    return $allresponses;
}


function choice_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function choice_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


function choice_extend_settings_navigation(settings_navigation $settings, navigation_node $choicenode) {
    global $PAGE;

    if (has_capability('mod/choice:readresponses', $PAGE->cm->context)) {

        $groupmode = groups_get_activity_groupmode($PAGE->cm);
        if ($groupmode) {
            groups_get_activity_group($PAGE->cm, true);
        }

        $choice = choice_get_choice($PAGE->cm->instance);

                $onlyactive = $choice->includeinactive ? false : true;

                $allresponses = choice_get_response_data($choice, $PAGE->cm, $groupmode, $onlyactive);

        $allusers = [];
        foreach($allresponses as $optionid => $userlist) {
            if ($optionid) {
                $allusers = array_merge($allusers, array_keys($userlist));
            }
        }
        $responsecount = count(array_unique($allusers));
        $choicenode->add(get_string("viewallresponses", "choice", $responsecount), new moodle_url('/mod/choice/report.php', array('id'=>$PAGE->cm->id)));
    }
}


function choice_get_completion_state($course, $cm, $userid, $type) {
    global $CFG,$DB;

        $choice = $DB->get_record('choice', array('id'=>$cm->instance), '*',
            MUST_EXIST);

        if($choice->completionsubmit) {
        return $DB->record_exists('choice_answers', array(
                'choiceid'=>$choice->id, 'userid'=>$userid));
    } else {
                return $type;
    }
}


function choice_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-choice-*'=>get_string('page-mod-choice-x', 'choice'));
    return $module_pagetype;
}


function choice_print_overview($courses, &$htmlarray) {
    global $USER, $DB, $OUTPUT;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return;
    }
    if (!$choices = get_all_instances_in_courses('choice', $courses)) {
        return;
    }

    $now = time();
    foreach ($choices as $choice) {
        if ($choice->timeclose != 0                                                  and $choice->timeclose >= $now                                           and ($choice->timeopen == 0 or $choice->timeopen <= $now)) { 
                        $class = (!$choice->visible) ? 'dimmed' : '';

                        $url = new moodle_url('/mod/choice/view.php', array('id' => $choice->coursemodule));
            $url = html_writer::link($url, format_string($choice->name), array('class' => $class));
            $str = $OUTPUT->box(get_string('choiceactivityname', 'choice', $url), 'name');

                         $str .= $OUTPUT->box(get_string('choicecloseson', 'choice', userdate($choice->timeclose)), 'info');

                        if (has_capability('mod/choice:readresponses', context_module::instance($choice->coursemodule))) {
                $attempts = $DB->count_records_sql('SELECT COUNT(DISTINCT userid) FROM {choice_answers} WHERE choiceid = ?',
                    [$choice->id]);
                $url = new moodle_url('/mod/choice/report.php', ['id' => $choice->coursemodule]);
                $str .= $OUTPUT->box(html_writer::link($url, get_string('viewallresponses', 'choice', $attempts)), 'info');

            } else if (has_capability('mod/choice:choose', context_module::instance($choice->coursemodule))) {
                                $answers = $DB->count_records('choice_answers', array('choiceid' => $choice->id, 'userid' => $USER->id));
                if ($answers > 0) {
                                        $str = '';
                } else {
                                        $str .= $OUTPUT->box(get_string('notanswered', 'choice'), 'info');
                }
            } else {
                                $str = '';
            }

                        if (!empty($str)) {
                                $str = $OUTPUT->box($str, 'choice overview');

                if (empty($htmlarray[$choice->course]['choice'])) {
                    $htmlarray[$choice->course]['choice'] = $str;
                } else {
                    $htmlarray[$choice->course]['choice'] .= $str;
                }
            }
        }
    }
    return;
}



function choice_get_my_response($choice) {
    global $DB, $USER;
    return $DB->get_records('choice_answers', array('choiceid' => $choice->id, 'userid' => $USER->id));
}



function choice_get_all_responses($choice) {
    global $DB;
    return $DB->get_records('choice_answers', array('choiceid' => $choice->id));
}



function choice_can_view_results($choice, $current = null, $choiceopen = null) {

    if (is_null($choiceopen)) {
        $timenow = time();
        if ($choice->timeclose != 0 && $timenow > $choice->timeclose) {
            $choiceopen = false;
        } else {
            $choiceopen = true;
        }
    }
    if (empty($current)) {
        $current = choice_get_my_response($choice);
    }

    if ($choice->showresults == CHOICE_SHOWRESULTS_ALWAYS or
       ($choice->showresults == CHOICE_SHOWRESULTS_AFTER_ANSWER and !empty($current)) or
       ($choice->showresults == CHOICE_SHOWRESULTS_AFTER_CLOSE and !$choiceopen)) {
        return true;
    }
    return false;
}


function choice_view($choice, $course, $cm, $context) {

        $params = array(
        'context' => $context,
        'objectid' => $choice->id
    );

    $event = \mod_choice\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('choice', $choice);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


function choice_get_availability_status($choice) {
    $available = true;
    $warnings = array();

    if ($choice->timeclose != 0) {
        $timenow = time();

        if ($choice->timeopen > $timenow) {
            $available = false;
            $warnings['notopenyet'] = userdate($choice->timeopen);
        } else if ($timenow > $choice->timeclose) {
            $available = false;
            $warnings['expired'] = userdate($choice->timeclose);
        }
    }
    if (!$choice->allowupdate && choice_get_my_response($choice)) {
        $available = false;
        $warnings['choicesaved'] = '';
    }

        return array($available, $warnings);
}
