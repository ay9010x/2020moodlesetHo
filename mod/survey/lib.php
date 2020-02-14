<?php





global $SURVEY_GHEIGHT;
$SURVEY_GHEIGHT = 500;

global $SURVEY_GWIDTH;
$SURVEY_GWIDTH  = 900;

global $SURVEY_QTYPE;
$SURVEY_QTYPE = array (
        "-3" => "Virtual Actual and Preferred",
        "-2" => "Virtual Preferred",
        "-1" => "Virtual Actual",
         "0" => "Text",
         "1" => "Actual",
         "2" => "Preferred",
         "3" => "Actual and Preferred",
        );


define("SURVEY_COLLES_ACTUAL",           "1");
define("SURVEY_COLLES_PREFERRED",        "2");
define("SURVEY_COLLES_PREFERRED_ACTUAL", "3");
define("SURVEY_ATTLS",                   "4");
define("SURVEY_CIQ",                     "5");



function survey_add_instance($survey) {
    global $DB;

    if (!$template = $DB->get_record("survey", array("id"=>$survey->template))) {
        return 0;
    }

    $survey->questions    = $template->questions;
    $survey->timecreated  = time();
    $survey->timemodified = $survey->timecreated;

    return $DB->insert_record("survey", $survey);

}


function survey_update_instance($survey) {
    global $DB;

    if (!$template = $DB->get_record("survey", array("id"=>$survey->template))) {
        return 0;
    }

    $survey->id           = $survey->instance;
    $survey->questions    = $template->questions;
    $survey->timemodified = time();

    return $DB->update_record("survey", $survey);
}


function survey_delete_instance($id) {
    global $DB;

    if (! $survey = $DB->get_record("survey", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("survey_analysis", array("survey"=>$survey->id))) {
        $result = false;
    }

    if (! $DB->delete_records("survey_answers", array("survey"=>$survey->id))) {
        $result = false;
    }

    if (! $DB->delete_records("survey", array("id"=>$survey->id))) {
        $result = false;
    }

    return $result;
}


function survey_user_outline($course, $user, $mod, $survey) {
    global $DB;

    if ($answers = $DB->get_records("survey_answers", array('survey'=>$survey->id, 'userid'=>$user->id))) {
        $lastanswer = array_pop($answers);

        $result = new stdClass();
        $result->info = get_string("done", "survey");
        $result->time = $lastanswer->time;
        return $result;
    }
    return NULL;
}


function survey_user_complete($course, $user, $mod, $survey) {
    global $CFG, $DB, $OUTPUT;

    if (survey_already_done($survey->id, $user->id)) {
        if ($survey->template == SURVEY_CIQ) {             $table = new html_table();
            $table->align = array("left", "left");

            $questions = $DB->get_records_list("survey_questions", "id", explode(',', $survey->questions));
            $questionorder = explode(",", $survey->questions);

            foreach ($questionorder as $key=>$val) {
                $question = $questions[$val];
                $questiontext = get_string($question->shorttext, "survey");

                if ($answer = survey_get_user_answer($survey->id, $question->id, $user->id)) {
                    $answertext = "$answer->answer1";
                } else {
                    $answertext = "No answer";
                }
                $table->data[] = array("<b>$questiontext</b>", s($answertext));
            }
            echo html_writer::table($table);

        } else {

            survey_print_graph("id=$mod->id&amp;sid=$user->id&amp;type=student.png");
        }

    } else {
        print_string("notdone", "survey");
    }
}


function survey_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG, $DB, $OUTPUT;

    $modinfo = get_fast_modinfo($course);
    $ids = array();
    foreach ($modinfo->cms as $cm) {
        if ($cm->modname != 'survey') {
            continue;
        }
        if (!$cm->uservisible) {
            continue;
        }
        $ids[$cm->instance] = $cm->instance;
    }

    if (!$ids) {
        return false;
    }

    $slist = implode(',', $ids); 
    $allusernames = user_picture::fields('u');
    $rs = $DB->get_recordset_sql("SELECT sa.userid, sa.survey, MAX(sa.time) AS time,
                                         $allusernames
                                    FROM {survey_answers} sa
                                    JOIN {user} u ON u.id = sa.userid
                                   WHERE sa.survey IN ($slist) AND sa.time > ?
                                GROUP BY sa.userid, sa.survey, $allusernames
                                ORDER BY time ASC", array($timestart));
    if (!$rs->valid()) {
        $rs->close();         return false;
    }

    $surveys = array();

    foreach ($rs as $survey) {
        $cm = $modinfo->instances['survey'][$survey->survey];
        $survey->name = $cm->name;
        $survey->cmid = $cm->id;
        $surveys[] = $survey;
    }
    $rs->close();

    if (!$surveys) {
        return false;
    }

    echo $OUTPUT->heading(get_string('newsurveyresponses', 'survey').':', 3);
    foreach ($surveys as $survey) {
        $url = $CFG->wwwroot.'/mod/survey/view.php?id='.$survey->cmid;
        print_recent_activity_note($survey->time, $survey, $survey->name, $url, false, $viewfullnames);
    }

    return true;
}



function survey_log_info($log) {
    global $DB;
    return $DB->get_record_sql("SELECT s.name, u.firstname, u.lastname, u.picture
                                  FROM {survey} s, {user} u
                                 WHERE s.id = ?  AND u.id = ?", array($log->info, $log->userid));
}


function survey_get_responses($surveyid, $groupid, $groupingid) {
    global $DB;

    $params = array('surveyid'=>$surveyid, 'groupid'=>$groupid, 'groupingid'=>$groupingid);

    if ($groupid) {
        $groupsjoin = "JOIN {groups_members} gm ON u.id = gm.userid AND gm.groupid = :groupid ";

    } else if ($groupingid) {
        $groupsjoin = "JOIN {groups_members} gm ON u.id = gm.userid
                       JOIN {groupings_groups} gg ON gm.groupid = gg.groupid AND gg.groupingid = :groupingid ";
    } else {
        $groupsjoin = "";
    }

    $userfields = user_picture::fields('u');
    return $DB->get_records_sql("SELECT $userfields, MAX(a.time) as time
                                   FROM {survey_answers} a
                                   JOIN {user} u ON a.userid = u.id
                            $groupsjoin
                                  WHERE a.survey = :surveyid
                               GROUP BY $userfields
                               ORDER BY time ASC", $params);
}


function survey_get_analysis($survey, $user) {
    global $DB;

    return $DB->get_record_sql("SELECT notes
                                  FROM {survey_analysis}
                                 WHERE survey=? AND userid=?", array($survey, $user));
}


function survey_update_analysis($survey, $user, $notes) {
    global $DB;

    return $DB->execute("UPDATE {survey_analysis}
                            SET notes=?
                          WHERE survey=?
                            AND userid=?", array($notes, $survey, $user));
}


function survey_get_user_answers($surveyid, $questionid, $groupid, $sort="sa.answer1,sa.answer2 ASC") {
    global $DB;

    $params = array('surveyid'=>$surveyid, 'questionid'=>$questionid);

    if ($groupid) {
        $groupfrom = ', {groups_members} gm';
        $groupsql  = 'AND gm.groupid = :groupid AND u.id = gm.userid';
        $params['groupid'] = $groupid;
    } else {
        $groupfrom = '';
        $groupsql  = '';
    }

    $userfields = user_picture::fields('u');
    return $DB->get_records_sql("SELECT sa.*, $userfields
                                   FROM {survey_answers} sa,  {user} u $groupfrom
                                  WHERE sa.survey = :surveyid
                                        AND sa.question = :questionid
                                        AND u.id = sa.userid $groupsql
                               ORDER BY $sort", $params);
}


function survey_get_user_answer($surveyid, $questionid, $userid) {
    global $DB;

    return $DB->get_record_sql("SELECT sa.*
                                  FROM {survey_answers} sa
                                 WHERE sa.survey = ?
                                       AND sa.question = ?
                                       AND sa.userid = ?", array($surveyid, $questionid, $userid));
}


function survey_add_analysis($survey, $user, $notes) {
    global $DB;

    $record = new stdClass();
    $record->survey = $survey;
    $record->userid = $user;
    $record->notes = $notes;

    return $DB->insert_record("survey_analysis", $record, false);
}

function survey_already_done($survey, $user) {
    global $DB;

    return $DB->record_exists("survey_answers", array("survey"=>$survey, "userid"=>$user));
}

function survey_count_responses($surveyid, $groupid, $groupingid) {
    if ($responses = survey_get_responses($surveyid, $groupid, $groupingid)) {
        return count($responses);
    } else {
        return 0;
    }
}


function survey_print_all_responses($cmid, $results, $courseid) {
    global $OUTPUT;
    $table = new html_table();
    $table->head  = array ("", get_string("name"),  get_string("time"));
    $table->align = array ("", "left", "left");
    $table->size = array (35, "", "" );

    foreach ($results as $a) {
        $table->data[] = array($OUTPUT->user_picture($a, array('courseid'=>$courseid)),
               html_writer::link("report.php?action=student&student=$a->id&id=$cmid", fullname($a)),
               userdate($a->time));
    }

    echo html_writer::table($table);
}


function survey_get_template_name($templateid) {
    global $DB;

    if ($templateid) {
        if ($ss = $DB->get_record("surveys", array("id"=>$templateid))) {
            return $ss->name;
        }
    } else {
        return "";
    }
}



function survey_shorten_name ($name, $numwords) {
    $words = explode(" ", $name);
    $output = '';
    for ($i=0; $i < $numwords; $i++) {
        $output .= $words[$i]." ";
    }
    return $output;
}


function survey_print_multi($question) {
    global $USER, $DB, $qnum, $checklist, $DB, $OUTPUT; 
    $stripreferthat = get_string("ipreferthat", "survey");
    $strifoundthat = get_string("ifoundthat", "survey");
    $strdefault    = get_string('notyetanswered', 'survey');
    $strresponses  = get_string('responses', 'survey');

    echo $OUTPUT->heading($question->text, 3);
    echo "\n<table width=\"90%\" cellpadding=\"4\" cellspacing=\"1\" border=\"0\" class=\"surveytable\">";

    $options = explode( ",", $question->options);
    $numoptions = count($options);

                            $oneanswer = ($question->type == 1 || $question->type == 2) ? true : false;

            if ($question->type == 2) {
        $P = "P";
    } else {
        $P = "";
    }

    echo "<tr class=\"smalltext\"><th scope=\"row\">$strresponses</th>";
    echo "<th scope=\"col\" class=\"hresponse\">". get_string('notyetanswered', 'survey'). "</th>";
    while (list ($key, $val) = each ($options)) {
        echo "<th scope=\"col\" class=\"hresponse\">$val</th>\n";
    }
    echo "</tr>\n";

    echo "<tr><th scope=\"col\" colspan=\"7\">$question->intro</th></tr>\n";

    $subquestions = survey_get_subquestions($question);

    foreach ($subquestions as $q) {
        $qnum++;
        if ($oneanswer) {
            $rowclass = survey_question_rowclass($qnum);
        } else {
            $rowclass = survey_question_rowclass(round($qnum / 2));
        }
        if ($q->text) {
            $q->text = get_string($q->text, "survey");
        }

        echo "<tr class=\"$rowclass rblock\">";
        if ($oneanswer) {
            echo "<th scope=\"row\" class=\"optioncell\">";
            echo "<b class=\"qnumtopcell\">$qnum</b> &nbsp; ";
            echo $q->text ."</th>\n";

            $default = get_accesshide($strdefault);
            echo "<td class=\"whitecell\"><label for=\"q$P$q->id\"><input type=\"radio\" name=\"q$P$q->id\" id=\"q$P" . $q->id . "_D\" value=\"0\" checked=\"checked\" />$default</label></td>";

            for ($i=1;$i<=$numoptions;$i++) {
                $hiddentext = get_accesshide($options[$i-1]);
                $id = "q$P" . $q->id . "_$i";
                echo "<td><label for=\"$id\"><input type=\"radio\" name=\"q$P$q->id\" id=\"$id\" value=\"$i\" />$hiddentext</label></td>";
            }
            $checklist["q$P$q->id"] = 0;

        } else {
            echo "<th scope=\"row\" class=\"optioncell\">";
            echo "<b class=\"qnumtopcell\">$qnum</b> &nbsp; ";
            $qnum++;
            echo "<span class=\"preferthat\">$stripreferthat</span> &nbsp; ";
            echo "<span class=\"option\">$q->text</span></th>\n";

            $default = get_accesshide($strdefault);
            echo '<td class="whitecell"><label for="qP'.$q->id.'"><input type="radio" name="qP'.$q->id.'" id="qP'.$q->id.'" value="0" checked="checked" />'.$default.'</label></td>';


            for ($i=1;$i<=$numoptions;$i++) {
                $hiddentext = get_accesshide($options[$i-1]);
                $id = "qP" . $q->id . "_$i";
                echo "<td><label for=\"$id\"><input type=\"radio\" name=\"qP$q->id\" id=\"$id\" value=\"$i\" />$hiddentext</label></td>";
            }
            echo "</tr>";

            echo "<tr class=\"$rowclass rblock\">";
            echo "<th scope=\"row\" class=\"optioncell\">";
            echo "<b class=\"qnumtopcell\">$qnum</b> &nbsp; ";
            echo "<span class=\"foundthat\">$strifoundthat</span> &nbsp; ";
            echo "<span class=\"option\">$q->text</span></th>\n";

            $default = get_accesshide($strdefault);
            echo '<td class="whitecell"><label for="q'. $q->id .'"><input type="radio" name="q'.$q->id. '" id="q'. $q->id .'" value="0" checked="checked" />'.$default.'</label></td>';

            for ($i=1;$i<=$numoptions;$i++) {
                $hiddentext = get_accesshide($options[$i-1]);
                $id = "q" . $q->id . "_$i";
                echo "<td><label for=\"$id\"><input type=\"radio\" name=\"q$q->id\" id=\"$id\" value=\"$i\" />$hiddentext</label></td>";
            }

            $checklist["qP$q->id"] = 0;
            $checklist["q$q->id"] = 0;
        }
        echo "</tr>\n";
    }
    echo "</table>";
}



function survey_print_single($question) {
    global $DB, $qnum, $OUTPUT;

    $rowclass = survey_question_rowclass(0);

    $qnum++;

    echo "<br />\n";
    echo "<table width=\"90%\" cellpadding=\"4\" cellspacing=\"0\">\n";
    echo "<tr class=\"$rowclass\">";
    echo "<th scope=\"row\" class=\"optioncell\"><label for=\"q$question->id\"><b class=\"qnumtopcell\">$qnum</b> &nbsp; ";
    echo "<span class=\"questioncell\">$question->text</span></label></th>\n";
    echo "<td class=\"questioncell smalltext\">\n";


    if ($question->type == 0) {                   echo "<textarea rows=\"3\" cols=\"30\" name=\"q$question->id\" id=\"q$question->id\">$question->options</textarea>";

    } else if ($question->type > 0) {             $strchoose = get_string("choose");
        echo "<select name=\"q$question->id\" id=\"q$question->id\">";
        echo "<option value=\"0\" selected=\"selected\">$strchoose...</option>";
        $options = explode( ",", $question->options);
        foreach ($options as $key => $val) {
            $key++;
            echo "<option value=\"$key\">$val</option>";
        }
        echo "</select>";

    } else if ($question->type < 0) {             $options = explode( ",", $question->options);
        echo $OUTPUT->notification("This question type not supported yet");
    }

    echo "</td></tr></table>";

}


function survey_question_rowclass($qnum) {

    if ($qnum) {
        return $qnum % 2 ? 'r0' : 'r1';
    } else {
        return 'r0';
    }
}


function survey_print_graph($url) {
    global $CFG, $SURVEY_GHEIGHT, $SURVEY_GWIDTH;

    echo "<img class='resultgraph' height=\"$SURVEY_GHEIGHT\" width=\"$SURVEY_GWIDTH\"".
         " src=\"$CFG->wwwroot/mod/survey/graph.php?$url\" alt=\"".get_string("surveygraph", "survey")."\" />";
}


function survey_get_view_actions() {
    return array('download','view all','view form','view graph','view report');
}


function survey_get_post_actions() {
    return array('submit');
}



function survey_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'surveyheader', get_string('modulenameplural', 'survey'));
    $mform->addElement('checkbox', 'reset_survey_answers', get_string('deleteallanswers','survey'));
    $mform->addElement('checkbox', 'reset_survey_analysis', get_string('deleteanalysis','survey'));
    $mform->disabledIf('reset_survey_analysis', 'reset_survey_answers', 'checked');
}


function survey_reset_course_form_defaults($course) {
    return array('reset_survey_answers'=>1, 'reset_survey_analysis'=>1);
}


function survey_reset_userdata($data) {
    global $DB;

    $componentstr = get_string('modulenameplural', 'survey');
    $status = array();

    $surveyssql = "SELECT ch.id
                     FROM {survey} ch
                    WHERE ch.course=?";
    $params = array($data->courseid);

    if (!empty($data->reset_survey_answers)) {
        $DB->delete_records_select('survey_answers', "survey IN ($surveyssql)", $params);
        $DB->delete_records_select('survey_analysis', "survey IN ($surveyssql)", $params);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallanswers', 'survey'), 'error'=>false);
    }

    if (!empty($data->reset_survey_analysis)) {
        $DB->delete_records_select('survey_analysis', "survey IN ($surveyssql)", $params);
        $status[] = array('component'=>$componentstr, 'item'=>get_string('deleteallanswers', 'survey'), 'error'=>false);
    }

        return $status;
}


function survey_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}


function survey_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


function survey_extend_settings_navigation($settings, $surveynode) {
    global $PAGE;

    if (has_capability('mod/survey:readresponses', $PAGE->cm->context)) {
        $responsesnode = $surveynode->add(get_string("responsereports", "survey"));

        $url = new moodle_url('/mod/survey/report.php', array('id' => $PAGE->cm->id, 'action'=>'summary'));
        $responsesnode->add(get_string("summary", "survey"), $url);

        $url = new moodle_url('/mod/survey/report.php', array('id' => $PAGE->cm->id, 'action'=>'scales'));
        $responsesnode->add(get_string("scales", "survey"), $url);

        $url = new moodle_url('/mod/survey/report.php', array('id' => $PAGE->cm->id, 'action'=>'questions'));
        $responsesnode->add(get_string("question", "survey"), $url);

        $url = new moodle_url('/mod/survey/report.php', array('id' => $PAGE->cm->id, 'action'=>'students'));
        $responsesnode->add(get_string('participants'), $url);

        if (has_capability('mod/survey:download', $PAGE->cm->context)) {
            $url = new moodle_url('/mod/survey/report.php', array('id' => $PAGE->cm->id, 'action'=>'download'));
            $surveynode->add(get_string('downloadresults', 'survey'), $url);
        }
    }
}


function survey_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-survey-*'=>get_string('page-mod-survey-x', 'survey'));
    return $module_pagetype;
}


function survey_view($survey, $course, $cm, $context, $viewed) {

        $params = array(
        'context' => $context,
        'objectid' => $survey->id,
        'courseid' => $course->id,
        'other' => array('viewed' => $viewed)
    );

    $event = \mod_survey\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('survey', $survey);
    $event->trigger();

        $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}


function survey_order_questions($questions, $questionorder) {

    $finalquestions = array();
    foreach ($questionorder as $qid) {
        $finalquestions[] = $questions[$qid];
    }
    return $finalquestions;
}


function survey_translate_question($question) {

    if ($question->text) {
        $question->text = get_string($question->text, "survey");
    }

    if ($question->shorttext) {
        $question->shorttext = get_string($question->shorttext, "survey");
    }

    if ($question->intro) {
        $question->intro = get_string($question->intro, "survey");
    }

    if ($question->options) {
        $question->options = get_string($question->options, "survey");
    }
    return $question;
}


function survey_get_questions($survey) {
    global $DB;

    $questionids = explode(',', $survey->questions);
    if (! $questions = $DB->get_records_list("survey_questions", "id", $questionids)) {
        throw new moodle_exception('cannotfindquestion', 'survey');
    }

    return survey_order_questions($questions, $questionids);
}


function survey_get_subquestions($question) {
    global $DB;

    $questionids = explode(',', $question->multi);
    $questions = $DB->get_records_list("survey_questions", "id", $questionids);

    return survey_order_questions($questions, $questionids);
}


function survey_save_answers($survey, $answersrawdata, $course, $context) {
    global $DB, $USER;

    $answers = array();

            foreach ($answersrawdata as $key => $val) {
        if ($key != "userid" && $key != "id") {
            if (substr($key, 0, 1) == "q") {
                $key = clean_param(substr($key, 1), PARAM_ALPHANUM);               }
            if (substr($key, 0, 1) == "P") {
                $realkey = (int) substr($key, 1);
                $answers[$realkey][1] = $val;
            } else {
                $answers[$key][0] = $val;
            }
        }
    }

        $timenow = time();
    $answerstoinsert = array();
    foreach ($answers as $key => $val) {
        if ($key != 'sesskey') {
            $newdata = new stdClass();
            $newdata->time = $timenow;
            $newdata->userid = $USER->id;
            $newdata->survey = $survey->id;
            $newdata->question = $key;
            if (!empty($val[0])) {
                $newdata->answer1 = $val[0];
            } else {
                $newdata->answer1 = "";
            }
            if (!empty($val[1])) {
                $newdata->answer2 = $val[1];
            } else {
                $newdata->answer2 = "";
            }

            $answerstoinsert[] = $newdata;
        }
    }

    if (!empty($answerstoinsert)) {
        $DB->insert_records("survey_answers", $answerstoinsert);
    }

    $params = array(
        'context' => $context,
        'courseid' => $course->id,
        'other' => array('surveyid' => $survey->id)
    );
    $event = \mod_survey\event\response_submitted::create($params);
    $event->trigger();
}
