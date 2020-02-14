<?php




require_once ("../../config.php");


$id    = required_param('id', PARAM_INT);    $type  = optional_param('type', 'xls', PARAM_ALPHA);
$group = optional_param('group', 0, PARAM_INT);

if (! $cm = get_coursemodule_from_id('survey', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/survey/download.php', array('id'=>$id, 'type'=>$type, 'group'=>$group));

require_login($course, false, $cm);
require_capability('mod/survey:download', $context) ;

if (! $survey = $DB->get_record("survey", array("id"=>$cm->instance))) {
    print_error('invalidsurveyid', 'survey');
}

$params = array(
    'objectid' => $survey->id,
    'context' => $context,
    'courseid' => $course->id,
    'other' => array('type' => $type, 'groupid' => $group)
);
$event = \mod_survey\event\report_downloaded::create($params);
$event->trigger();


$groupmode = groups_get_activity_groupmode($cm);   
if ($groupmode and $group) {
    $users = get_users_by_capability($context, 'mod/survey:participate', '', '', '', '', $group, null, false);
} else {
    $users = get_users_by_capability($context, 'mod/survey:participate', '', '', '', '', '', null, false);
    $group = false;
}

$order = explode(",", $survey->questions);

$questions = $DB->get_records_list("survey_questions", "id", $order);

$orderedquestions = array();

$virtualscales = false;
foreach ($order as $qid) {
    $orderedquestions[$qid] = $questions[$qid];
        if (!$virtualscales && $questions[$qid]->type < 0) {
        $virtualscales = true;
    }
}
$nestedorder = array();$preparray = array();

foreach ($orderedquestions as $qid=>$question) {
        if (!empty($question->multi)) {
        $actualqids = explode(",", $questions[$qid]->multi);
        foreach ($actualqids as $subqid) {
            if (!empty($orderedquestions[$subqid]->type)) {
                $orderedquestions[$subqid]->type = $questions[$qid]->type;
            }
        }
    } else {
        $actualqids = array($qid);
    }
    if ($virtualscales && $questions[$qid]->type < 0) {
        $nestedorder[$qid] = $actualqids;
    } else if (!$virtualscales && $question->type >= 0) {
        $nestedorder[$qid] = $actualqids;
    } else {
                $nestedorder[$qid] = array();
    }
}

$reversednestedorder = array();
foreach ($nestedorder as $qid=>$subqidarray) {
    foreach ($subqidarray as $subqui) {
        $reversednestedorder[$subqui] = $qid;
    }
}

$allquestions = array_merge($questions, $DB->get_records_list("survey_questions", "id", array_keys($reversednestedorder)));

$questions = array();
foreach($allquestions as $question) {
    $questions[$question->id] = $question;

        $questions[$question->id]->text = get_string($questions[$question->id]->text, "survey");
}
unset($allquestions);

if (! $surveyanswers = $DB->get_records("survey_answers", array("survey"=>$survey->id), "time ASC")) {
    print_error('cannotfindanswer', 'survey');
}

$results = array();

foreach ($surveyanswers as $surveyanswer) {
    if (!$group || isset($users[$surveyanswer->userid])) {
                $questionid = $surveyanswer->question;
        if (!array_key_exists($surveyanswer->userid, $results)) {
            $results[$surveyanswer->userid] = array('time'=>$surveyanswer->time);
        }
        $results[$surveyanswer->userid][$questionid]['answer1'] = $surveyanswer->answer1;
        $results[$surveyanswer->userid][$questionid]['answer2'] = $surveyanswer->answer2;
    }
}

$coursecontext = context_course::instance($course->id);
$courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));

if ($type == "ods") {
    require_once("$CFG->libdir/odslib.class.php");

    $downloadfilename = clean_filename(strip_tags($courseshortname.' '.format_string($survey->name, true))).'.ods';
    $workbook = new MoodleODSWorkbook("-");
    $workbook->send($downloadfilename);
    $myxls = $workbook->add_worksheet(core_text::substr(strip_tags(format_string($survey->name,true)), 0, 31));

    $header = array("surveyid","surveyname","userid","firstname","lastname","email","idnumber","time", "notes");
    $col=0;
    foreach ($header as $item) {
        $myxls->write_string(0,$col++,$item);
    }

    foreach ($nestedorder as $key => $nestedquestions) {
        foreach ($nestedquestions as $key2 => $qid) {
            $question = $questions[$qid];
            if ($question->type == "0" || $question->type == "1" || $question->type == "3" || $question->type == "-1")  {
                $myxls->write_string(0,$col++,"$question->text");
            }
            if ($question->type == "2" || $question->type == "3")  {
                $myxls->write_string(0,$col++,"$question->text (preferred)");
            }
        }
    }


    $row = 0;
    foreach ($results as $user => $rest) {
        $col = 0;
        $row++;
        if (! $u = $DB->get_record("user", array("id"=>$user))) {
            print_error('invaliduserid');
        }
        if ($n = $DB->get_record("survey_analysis", array("survey"=>$survey->id, "userid"=>$user))) {
            $notes = $n->notes;
        } else {
            $notes = "No notes made";
        }
        $myxls->write_string($row,$col++,$survey->id);
        $myxls->write_string($row,$col++,strip_tags(format_text($survey->name,true)));
        $myxls->write_string($row,$col++,$user);
        $myxls->write_string($row,$col++,$u->firstname);
        $myxls->write_string($row,$col++,$u->lastname);
        $myxls->write_string($row,$col++,$u->email);
        $myxls->write_string($row,$col++,$u->idnumber);
        $myxls->write_string($row,$col++, userdate($results[$user]["time"], "%d-%b-%Y %I:%M:%S %p") );
        $myxls->write_string($row,$col++,$notes);

        foreach ($nestedorder as $key => $nestedquestions) {
            foreach ($nestedquestions as $key2 => $qid) {
                $question = $questions[$qid];
                if ($question->type == "0" || $question->type == "1" || $question->type == "3" || $question->type == "-1")  {
                    $myxls->write_string($row,$col++, $results[$user][$qid]["answer1"] );
                }
                if ($question->type == "2" || $question->type == "3")  {
                    $myxls->write_string($row, $col++, $results[$user][$qid]["answer2"] );
                }
            }
        }
    }
    $workbook->close();

    exit;
}


if ($type == "xls") {
    require_once("$CFG->libdir/excellib.class.php");

    $downloadfilename = clean_filename(strip_tags($courseshortname.' '.format_string($survey->name,true))).'.xls';
    $workbook = new MoodleExcelWorkbook("-");
    $workbook->send($downloadfilename);
    $myxls = $workbook->add_worksheet(core_text::substr(strip_tags(format_string($survey->name,true)), 0, 31));

    $header = array("surveyid","surveyname","userid","firstname","lastname","email","idnumber","time", "notes");
    $col=0;
    foreach ($header as $item) {
        $myxls->write_string(0,$col++,$item);
    }

    foreach ($nestedorder as $key => $nestedquestions) {
        foreach ($nestedquestions as $key2 => $qid) {
            $question = $questions[$qid];

            if ($question->type == "0" || $question->type == "1" || $question->type == "3" || $question->type == "-1")  {
                $myxls->write_string(0,$col++,"$question->text");
            }
            if ($question->type == "2" || $question->type == "3")  {
                $myxls->write_string(0,$col++,"$question->text (preferred)");
            }
        }
    }


    $row = 0;
    foreach ($results as $user => $rest) {
        $col = 0;
        $row++;
        if (! $u = $DB->get_record("user", array("id"=>$user))) {
            print_error('invaliduserid');
        }
        if ($n = $DB->get_record("survey_analysis", array("survey"=>$survey->id, "userid"=>$user))) {
            $notes = $n->notes;
        } else {
            $notes = "No notes made";
        }
        $myxls->write_string($row,$col++,$survey->id);
        $myxls->write_string($row,$col++,strip_tags(format_text($survey->name,true)));
        $myxls->write_string($row,$col++,$user);
        $myxls->write_string($row,$col++,$u->firstname);
        $myxls->write_string($row,$col++,$u->lastname);
        $myxls->write_string($row,$col++,$u->email);
        $myxls->write_string($row,$col++,$u->idnumber);
        $myxls->write_string($row,$col++, userdate($results[$user]["time"], "%d-%b-%Y %I:%M:%S %p") );
        $myxls->write_string($row,$col++,$notes);

        foreach ($nestedorder as $key => $nestedquestions) {
            foreach ($nestedquestions as $key2 => $qid) {
                $question = $questions[$qid];
                if (($question->type == "0" || $question->type == "1" || $question->type == "3" || $question->type == "-1")
                    && array_key_exists($qid, $results[$user]) ){
                $myxls->write_string($row,$col++, $results[$user][$qid]["answer1"] );
            }
                if (($question->type == "2" || $question->type == "3")
                    && array_key_exists($qid, $results[$user]) ){
                $myxls->write_string($row, $col++, $results[$user][$qid]["answer2"] );
            }
        }
    }
    }
    $workbook->close();

    exit;
}



header("Content-Type: application/download\n");

$downloadfilename = clean_filename(strip_tags($courseshortname.' '.format_string($survey->name,true)));
header("Content-Disposition: attachment; filename=\"$downloadfilename.txt\"");


echo "surveyid    surveyname    userid    firstname    lastname    email    idnumber    time    ";

foreach ($nestedorder as $key => $nestedquestions) {
    foreach ($nestedquestions as $key2 => $qid) {
        $question = $questions[$qid];
    if ($question->type == "0" || $question->type == "1" || $question->type == "3" || $question->type == "-1")  {
        echo "$question->text    ";
    }
    if ($question->type == "2" || $question->type == "3")  {
         echo "$question->text (preferred)    ";
    }
}
}
echo "\n";

foreach ($results as $user => $rest) {
    if (! $u = $DB->get_record("user", array("id"=>$user))) {
        print_error('invaliduserid');
    }
    echo $survey->id."\t";
    echo strip_tags(format_string($survey->name,true))."\t";
    echo $user."\t";
    echo $u->firstname."\t";
    echo $u->lastname."\t";
    echo $u->email."\t";
    echo $u->idnumber."\t";
    echo userdate($results[$user]["time"], "%d-%b-%Y %I:%M:%S %p")."\t";

    foreach ($nestedorder as $key => $nestedquestions) {
        foreach ($nestedquestions as $key2 => $qid) {
            $question = $questions[$qid];

            if ($question->type == "0" || $question->type == "1" || $question->type == "3" || $question->type == "-1")  {
                echo $results[$user][$qid]["answer1"]."    ";
            }
            if ($question->type == "2" || $question->type == "3")  {
                echo $results[$user][$qid]["answer2"]."    ";
            }
        }
    }
    echo "\n";
}

exit;
