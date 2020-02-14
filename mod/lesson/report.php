<?php




require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');
require_once($CFG->dirroot.'/mod/lesson/pagetypes/branchtable.php'); 
$id     = required_param('id', PARAM_INT);    $pageid = optional_param('pageid', null, PARAM_INT);    $action = optional_param('action', 'reportoverview', PARAM_ALPHA);  $nothingtodisplay = false;

$cm = get_coursemodule_from_id('lesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$lesson = new lesson($DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST));

require_login($course, false, $cm);

$currentgroup = groups_get_activity_group($cm, true);

$context = context_module::instance($cm->id);
require_capability('mod/lesson:viewreports', $context);

$url = new moodle_url('/mod/lesson/report.php', array('id'=>$id));
$url->param('action', $action);
if ($pageid !== null) {
    $url->param('pageid', $pageid);
}
$PAGE->set_url($url);
if ($action == 'reportoverview') {
    $PAGE->navbar->add(get_string('reports', 'lesson'));
    $PAGE->navbar->add(get_string('overview', 'lesson'));
}

$lessonoutput = $PAGE->get_renderer('mod_lesson');

if ($action === 'delete') {
        if (has_capability('mod/lesson:edit', $context) and $form = data_submitted() and confirm_sesskey()) {
            if (!empty($form->attempts)) {
            foreach ($form->attempts as $userid => $tries) {
                                                                                                                $modifier = 0;

                foreach ($tries as $try => $junk) {
                    $try -= $modifier;

                                    $params = array ("userid" => $userid, "lessonid" => $lesson->id);
                    $timers = $DB->get_records_sql("SELECT id FROM {lesson_timer}
                                                     WHERE userid = :userid AND lessonid = :lessonid
                                                  ORDER BY starttime", $params, $try, 1);
                    if ($timers) {
                        $timer = reset($timers);
                        $DB->delete_records('lesson_timer', array('id' => $timer->id));
                    }

                                        $grades = $DB->get_records_sql("SELECT id FROM {lesson_grades}
                                                     WHERE userid = :userid AND lessonid = :lessonid
                                                  ORDER BY completed", $params, $try, 1);

                    if ($grades) {
                        $grade = reset($grades);
                        $DB->delete_records('lesson_grades', array('id' => $grade->id));
                    }

                                    $DB->delete_records('lesson_attempts', array('userid' => $userid, 'lessonid' => $lesson->id, 'retry' => $try));
                    $DB->execute("UPDATE {lesson_attempts} SET retry = retry - 1 WHERE userid = ? AND lessonid = ? AND retry > ?", array($userid, $lesson->id, $try));

                                    $DB->delete_records('lesson_branch', array('userid' => $userid, 'lessonid' => $lesson->id, 'retry' => $try));
                    $DB->execute("UPDATE {lesson_branch} SET retry = retry - 1 WHERE userid = ? AND lessonid = ? AND retry > ?", array($userid, $lesson->id, $try));

                                    lesson_update_grades($lesson, $userid);

                    $modifier++;
                }
            }
        }
    }
    redirect(new moodle_url($PAGE->url, array('action'=>'reportoverview')));

} else if ($action === 'reportoverview') {
    

        $branchcount = $DB->count_records('lesson_pages', array('lessonid' => $lesson->id, 'qtype' => LESSON_PAGE_BRANCHTABLE));
    $questioncount = ($DB->count_records('lesson_pages', array('lessonid' => $lesson->id)) - $branchcount);

        $attempts = $DB->record_exists('lesson_attempts', array('lessonid' => $lesson->id));
    $branches = $DB->record_exists('lesson_branch', array('lessonid' => $lesson->id));
    $timer = $DB->record_exists('lesson_timer', array('lessonid' => $lesson->id));
    if ($attempts or $branches or $timer) {
        list($esql, $params) = get_enrolled_sql($context, '', $currentgroup, true);
        list($sort, $sortparams) = users_order_by_sql('u');

        $params['a1lessonid'] = $lesson->id;
        $params['b1lessonid'] = $lesson->id;
        $params['c1lessonid'] = $lesson->id;
        $ufields = user_picture::fields('u');
        $sql = "SELECT DISTINCT $ufields
                FROM {user} u
                JOIN (
                    SELECT userid, lessonid FROM {lesson_attempts} a1
                    WHERE a1.lessonid = :a1lessonid
                        UNION
                    SELECT userid, lessonid FROM {lesson_branch} b1
                    WHERE b1.lessonid = :b1lessonid
                        UNION
                    SELECT userid, lessonid FROM {lesson_timer} c1
                    WHERE c1.lessonid = :c1lessonid
                    ) a ON u.id = a.userid
                JOIN ($esql) ue ON ue.id = a.userid
                ORDER BY $sort";

        $students = $DB->get_recordset_sql($sql, $params);
        if (!$students->valid()) {
            $students->close();
            $nothingtodisplay = true;
        }
    } else {
        $nothingtodisplay = true;
    }

    if ($nothingtodisplay) {
        echo $lessonoutput->header($lesson, $cm, $action, false, null, get_string('nolessonattempts', 'lesson'));
        if (!empty($currentgroup)) {
            $groupname = groups_get_group_name($currentgroup);
            echo $OUTPUT->notification(get_string('nolessonattemptsgroup', 'lesson', $groupname));
        } else {
            echo $OUTPUT->notification(get_string('nolessonattempts', 'lesson'));
        }
        groups_print_activity_menu($cm, $url);
        echo $OUTPUT->footer();
        exit();
    }

    if (! $grades = $DB->get_records('lesson_grades', array('lessonid' => $lesson->id), 'completed')) {
        $grades = array();
    }

    if (! $times = $DB->get_records('lesson_timer', array('lessonid' => $lesson->id), 'starttime')) {
        $times = array();
    }

    echo $lessonoutput->header($lesson, $cm, $action, false, null, get_string('overview', 'lesson'));
    groups_print_activity_menu($cm, $url);

    $course_context = context_course::instance($course->id);
    if (has_capability('gradereport/grader:view', $course_context) && has_capability('moodle/grade:viewall', $course_context)) {
        $seeallgradeslink = new moodle_url('/grade/report/grader/index.php', array('id'=>$course->id));
        $seeallgradeslink = html_writer::link($seeallgradeslink, get_string('seeallcoursegrades', 'grades'));
        echo $OUTPUT->box($seeallgradeslink, 'allcoursegrades');
    }

        $studentdata = array();

    $attempts = $DB->get_recordset('lesson_attempts', array('lessonid' => $lesson->id), 'timeseen');
    foreach ($attempts as $attempt) {
                if (empty($studentdata[$attempt->userid]) || empty($studentdata[$attempt->userid][$attempt->retry])) {
                        $n = 0;
            $timestart = 0;
            $timeend = 0;
            $usergrade = null;
            $eol = false;

                        foreach($grades as $grade) {
                                if ($grade->userid == $attempt->userid) {
                                        if ($n == $attempt->retry) {
                                                $usergrade = round($grade->grade, 2);                         break;
                    }
                    $n++;                 }
            }
            $n = 0;
                        foreach($times as $time) {
                                if ($time->userid == $attempt->userid) {
                                        if ($n == $attempt->retry) {
                                                $timeend = $time->lessontime;
                        $timestart = $time->starttime;
                        $eol = $time->completed;
                        break;
                    }
                    $n++;                 }
            }

                                    $studentdata[$attempt->userid][$attempt->retry] = array( "timestart" => $timestart,
                                                                    "timeend" => $timeend,
                                                                    "grade" => $usergrade,
                                                                    "end" => $eol,
                                                                    "try" => $attempt->retry,
                                                                    "userid" => $attempt->userid);
        }
    }
    $attempts->close();

    $branches = $DB->get_recordset('lesson_branch', array('lessonid' => $lesson->id), 'timeseen');
    foreach ($branches as $branch) {
                if (empty($studentdata[$branch->userid]) || empty($studentdata[$branch->userid][$branch->retry])) {
                        $n = 0;
            $timestart = 0;
            $timeend = 0;
            $usergrade = null;
            $eol = false;
                        foreach ($times as $time) {
                                if ($time->userid == $branch->userid) {
                                        if ($n == $branch->retry) {
                                                $timeend = $time->lessontime;
                        $timestart = $time->starttime;
                        $eol = $time->completed;
                        break;
                    }
                    $n++;                 }
            }

                                    $studentdata[$branch->userid][$branch->retry] = array( "timestart" => $timestart,
                                                                    "timeend" => $timeend,
                                                                    "grade" => $usergrade,
                                                                    "end" => $eol,
                                                                    "try" => $branch->retry,
                                                                    "userid" => $branch->userid);
        }
    }
    $branches->close();

        foreach ($times as $time) {
        $endoflesson = $time->completed;
                if (isset($studentdata[$time->userid])) {
            $foundmatch = false;
            $n = 0;
            foreach ($studentdata[$time->userid] as $key => $value) {
                if ($value['timestart'] == $time->starttime) {
                                        $foundmatch = true;
                    break;
                }
            }
            $n = count($studentdata[$time->userid]) + 1;
            if (!$foundmatch) {
                                $studentdata[$time->userid][] = array(
                                "timestart" => $time->starttime,
                                "timeend" => $time->lessontime,
                                "grade" => null,
                                "end" => $endoflesson,
                                "try" => $n,
                                "userid" => $time->userid
                            );
            }
        } else {
            $studentdata[$time->userid][] = array(
                                "timestart" => $time->starttime,
                                "timeend" => $time->lessontime,
                                "grade" => null,
                                "end" => $endoflesson,
                                "try" => 0,
                                "userid" => $time->userid
                            );
        }
    }
        if ($branchcount > 0 AND $questioncount == 0) {
                $lessonscored = false;
    } else {
                $lessonscored = true;
    }
        $numofattempts = 0;
    $avescore      = 0;
    $avetime       = 0;
    $highscore     = null;
    $lowscore      = null;
    $hightime      = null;
    $lowtime       = null;

    $table = new html_table();

        if ($lessonscored) {
        $table->head = array(get_string('name'), get_string('attempts', 'lesson'), get_string('highscore', 'lesson'));
    } else {
        $table->head = array(get_string('name'), get_string('attempts', 'lesson'));
    }
    $table->align = array('center', 'left', 'left');
    $table->wrap = array('nowrap', 'nowrap', 'nowrap');
    $table->attributes['class'] = 'standardtable generaltable';
    $table->size = array(null, '70%', null);

            foreach ($students as $student) {
                if (array_key_exists($student->id, $studentdata)) {
                        $attempts = array();
                        $bestgrade = 0;
            $bestgradefound = false;
                        $tries = $studentdata[$student->id];
            $studentname = fullname($student, true);
            foreach ($tries as $try) {
                            if (has_capability('mod/lesson:edit', $context)) {
                    $temp = '<input type="checkbox" id="attempts" name="attempts['.$try['userid'].']['.$try['try'].']" /> ';
                } else {
                    $temp = '';
                }

                $temp .= "<a href=\"report.php?id=$cm->id&amp;action=reportdetail&amp;userid=".$try['userid']
                        .'&amp;try='.$try['try'].'" class="lesson-attempt-link">';
                if ($try["grade"] !== null) {                                         $timetotake = $try["timeend"] - $try["timestart"];

                    $temp .= $try["grade"]."%";
                    $bestgradefound = true;
                    if ($try["grade"] > $bestgrade) {
                        $bestgrade = $try["grade"];
                    }
                    $temp .= "&nbsp;".userdate($try["timestart"]);
                    $temp .= ",&nbsp;(".format_time($timetotake).")</a>";
                } else {
                    if ($try["end"]) {
                                                $temp .= "&nbsp;".userdate($try["timestart"]);
                        $timetotake = $try["timeend"] - $try["timestart"];
                        $temp .= ",&nbsp;(".format_time($timetotake).")</a>";
                    } else {
                                                $temp .= get_string("notcompleted", "lesson");
                        if ($try['timestart'] !== 0) {
                                                        $temp .= "&nbsp;".userdate($try["timestart"]);
                        }
                        $temp .= "</a>";
                        $timetotake = null;
                    }
                }
                                $attempts[] = $temp;

                                if ($try["end"]) {
                                        $numofattempts++;
                    $avetime += $timetotake;
                    if ($timetotake > $hightime || $hightime == null) {
                        $hightime = $timetotake;
                    }
                    if ($timetotake < $lowtime || $lowtime == null) {
                        $lowtime = $timetotake;
                    }
                    if ($try["grade"] !== null) {
                                                $avescore += $try["grade"];
                        if ($try["grade"] > $highscore || $highscore === null) {
                            $highscore = $try["grade"];
                        }
                        if ($try["grade"] < $lowscore || $lowscore === null) {
                            $lowscore = $try["grade"];
                        }

                    }
                }
            }
                        $attempts = implode("<br />\n", $attempts);

            if ($lessonscored) {
                                $bestgrade = $bestgrade."%";
                $table->data[] = array($studentname, $attempts, $bestgrade);
            } else {
                                $table->data[] = array($studentname, $attempts);
            }
        }
    }
    $students->close();
        if (has_capability('mod/lesson:edit', $context)) {
        echo  "<form id=\"theform\" method=\"post\" action=\"report.php\">\n
               <input type=\"hidden\" name=\"sesskey\" value=\"".sesskey()."\" />\n
               <input type=\"hidden\" name=\"id\" value=\"$cm->id\" />\n";
    }
    echo html_writer::table($table);
    if (has_capability('mod/lesson:edit', $context)) {
        $checklinks  = '<a href="javascript: checkall();">'.get_string('selectall').'</a> / ';
        $checklinks .= '<a href="javascript: checknone();">'.get_string('deselectall').'</a>';
        $checklinks .= html_writer::label('action', 'menuaction', false, array('class' => 'accesshide'));
        $checklinks .= html_writer::select(array('delete' => get_string('deleteselected')), 'action', 0, array(''=>'choosedots'), array('id'=>'actionid', 'class' => 'autosubmit'));
        $PAGE->requires->yui_module('moodle-core-formautosubmit',
            'M.core.init_formautosubmit',
            array(array('selectid' => 'actionid', 'nothing' => false))
        );
        echo $OUTPUT->box($checklinks, 'center');
        echo '</form>';
    }

        if ($avetime == null) {
        $avetime = get_string("notcompleted", "lesson");
    } else {
        $avetime = format_float($avetime/$numofattempts, 0);
        $avetime = format_time($avetime);
    }
    if ($hightime == null) {
        $hightime = get_string("notcompleted", "lesson");
    } else {
        $hightime = format_time($hightime);
    }
    if ($lowtime == null) {
        $lowtime = get_string("notcompleted", "lesson");
    } else {
        $lowtime = format_time($lowtime);
    }

    if ($lessonscored) {
        if ($numofattempts == 0) {
            $avescore = get_string("notcompleted", "lesson");
        } else {
            $avescore = format_float($avescore / $numofattempts, 2) . '%';
        }
        if ($highscore === null) {
            $highscore = get_string("notcompleted", "lesson");
        } else {
            $highscore .= '%';
        }
        if ($lowscore === null) {
            $lowscore = get_string("notcompleted", "lesson");
        } else {
            $lowscore .= '%';
        }

                echo $OUTPUT->heading(get_string('lessonstats', 'lesson'), 3);
        $stattable = new html_table();
        $stattable->head = array(get_string('averagescore', 'lesson'), get_string('averagetime', 'lesson'),
                                get_string('highscore', 'lesson'), get_string('lowscore', 'lesson'),
                                get_string('hightime', 'lesson'), get_string('lowtime', 'lesson'));
        $stattable->align = array('center', 'center', 'center', 'center', 'center', 'center');
        $stattable->wrap = array('nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap', 'nowrap');
        $stattable->attributes['class'] = 'standardtable generaltable';
        $stattable->data[] = array($avescore, $avetime, $highscore, $lowscore, $hightime, $lowtime);

    } else {
                echo $OUTPUT->heading(get_string('lessonstats', 'lesson'), 3);
        $stattable = new html_table();
        $stattable->head = array(get_string('averagetime', 'lesson'), get_string('hightime', 'lesson'),
                                get_string('lowtime', 'lesson'));
        $stattable->align = array('center', 'center', 'center');
        $stattable->wrap = array('nowrap', 'nowrap', 'nowrap');
        $stattable->attributes['class'] = 'standardtable generaltable';
        $stattable->data[] = array($avetime, $hightime, $lowtime);
    }

    echo html_writer::table($stattable);
} else if ($action === 'reportdetail') {
    
    echo $lessonoutput->header($lesson, $cm, $action, false, null, get_string('detailedstats', 'lesson'));
    groups_print_activity_menu($cm, $url);

    $course_context = context_course::instance($course->id);
    if (has_capability('gradereport/grader:view', $course_context) && has_capability('moodle/grade:viewall', $course_context)) {
        $seeallgradeslink = new moodle_url('/grade/report/grader/index.php', array('id'=>$course->id));
        $seeallgradeslink = html_writer::link($seeallgradeslink, get_string('seeallcoursegrades', 'grades'));
        echo $OUTPUT->box($seeallgradeslink, 'allcoursegrades');
    }

    $formattextdefoptions = new stdClass;
    $formattextdefoptions->para = false;      $formattextdefoptions->overflowdiv = true;

    $userid = optional_param('userid', null, PARAM_INT);     $try    = optional_param('try', null, PARAM_INT);

    if (!empty($userid)) {
                $lesson->update_effective_access($userid);
    }

    $lessonpages = $lesson->load_all_pages();
    foreach ($lessonpages as $lessonpage) {
        if ($lessonpage->prevpageid == 0) {
            $pageid = $lessonpage->id;
        }
    }

        $firstpageid = $pageid;
    $pagestats = array();
    while ($pageid != 0) {         $page = $lessonpages[$pageid];
        $params = array ("lessonid" => $lesson->id, "pageid" => $page->id);
        if ($allanswers = $DB->get_records_select("lesson_attempts", "lessonid = :lessonid AND pageid = :pageid", $params, "timeseen")) {
                        $orderedanswers = array();
            foreach ($allanswers as $singleanswer) {
                                $orderedanswers[$singleanswer->userid][$singleanswer->retry][] = $singleanswer;
            }
                        foreach ($orderedanswers as $orderedanswer) {
                foreach($orderedanswer as $tries) {
                    $page->stats($pagestats, $tries);
                }
            }
        } else {
                    }
                $pageid = $page->nextpageid;
    }

    $manager = lesson_page_type_manager::get($lesson);
    $qtypes = $manager->get_page_type_strings();

    $answerpages = array();
    $answerpage = "";
    $pageid = $firstpageid;
                        while ($pageid != 0) {         $page = $lessonpages[$pageid];
        $answerpage = new stdClass;
        $data ='';

        $answerdata = new stdClass;
                $answerdata->score = null;
        $answerdata->response = null;
        $answerdata->responseformat = FORMAT_PLAIN;

        $answerpage->title = format_string($page->title);

        $options = new stdClass;
        $options->noclean = true;
        $options->overflowdiv = true;
        $options->context = $context;
        $answerpage->contents = format_text($page->contents, $page->contentsformat, $options);

        $answerpage->qtype = $qtypes[$page->qtype].$page->option_description_string();
        $answerpage->grayout = $page->grayout;
        $answerpage->context = $context;

        if (empty($userid)) {
                        $answerpage->grayout = 0;
            $useranswer = null;
        } elseif ($useranswers = $DB->get_records("lesson_attempts",array("lessonid"=>$lesson->id, "userid"=>$userid, "retry"=>$try,"pageid"=>$page->id), "timeseen")) {
                                    $i = 0;
            foreach ($useranswers as $userattempt) {
                $useranswer = $userattempt;
                $i++;
                if ($lesson->maxattempts == $i) {
                    break;                 }
            }
        } else {
                        $answerpage->grayout = 1;
            $useranswer = null;
        }
        $i = 0;
        $n = 0;
        $answerpages[] = $page->report_answers(clone($answerpage), clone($answerdata), $useranswer, $pagestats, $i, $n);
        $pageid = $page->nextpageid;
    }

        $table = new html_table();
    $table->wrap = array();
    $table->width = "60%";
    if (!empty($userid)) {
        
                                                                    echo $OUTPUT->heading(get_string('attempt', 'lesson', $try+1), 3);

        $table->head = array();
        $table->align = array('right', 'left');
        $table->attributes['class'] = 'compacttable generaltable';

        $params = array("lessonid"=>$lesson->id, "userid"=>$userid);
        if (!$grades = $DB->get_records_select("lesson_grades", "lessonid = :lessonid and userid = :userid", $params, "completed", "*", $try, 1)) {
            $grade = -1;
            $completed = -1;
        } else {
            $grade = current($grades);
            $completed = $grade->completed;
            $grade = round($grade->grade, 2);
        }
        if (!$times = $DB->get_records_select("lesson_timer", "lessonid = :lessonid and userid = :userid", $params, "starttime", "*", $try, 1)) {
            $timetotake = -1;
        } else {
            $timetotake = current($times);
            $timetotake = $timetotake->lessontime - $timetotake->starttime;
        }

        if ($timetotake == -1 || $completed == -1 || $grade == -1) {
            $table->align = array("center");

            $table->data[] = array(get_string("notcompleted", "lesson"));
        } else {
            $user = $DB->get_record('user', array('id' => $userid));

            $gradeinfo = lesson_grade($lesson, $try, $user->id);

            $table->data[] = array(get_string('name').':', $OUTPUT->user_picture($user, array('courseid'=>$course->id)).fullname($user, true));
            $table->data[] = array(get_string("timetaken", "lesson").":", format_time($timetotake));
            $table->data[] = array(get_string("completed", "lesson").":", userdate($completed));
            $table->data[] = array(get_string('rawgrade', 'lesson').':', $gradeinfo->earned.'/'.$gradeinfo->total);
            $table->data[] = array(get_string("grade", "lesson").":", $grade."%");
        }
        echo html_writer::table($table);

                $table->attributes['class'] = '';
    }


    $table->align = array('left', 'left');
    $table->size = array('70%', null);
    $table->attributes['class'] = 'compacttable generaltable';

    foreach ($answerpages as $page) {
        unset($table->data);
        if ($page->grayout) {             $fontstart = "<span class=\"dimmed\">";
            $fontend = "</font>";
            $fontstart2 = $fontstart;
            $fontend2 = $fontend;
        } else {
            $fontstart = "";
            $fontend = "";
            $fontstart2 = "";
            $fontend2 = "";
        }

        $table->head = array($fontstart2.$page->qtype.": ".format_string($page->title).$fontend2, $fontstart2.get_string("classstats", "lesson").$fontend2);
        $table->data[] = array($fontstart.get_string("question", "lesson").": <br />".$fontend.$fontstart2.$page->contents.$fontend2, " ");
        $table->data[] = array($fontstart.get_string("answer", "lesson").":".$fontend, ' ');
                if (!empty($page->answerdata)) {
            foreach ($page->answerdata->answers as $answer){
                $modified = array();
                foreach ($answer as $single) {
                                        $modified[] = $fontstart2.$single.$fontend2;
                }
                $table->data[] = $modified;
            }
            if (isset($page->answerdata->response)) {
                $table->data[] = array($fontstart.get_string("response", "lesson").": <br />".$fontend
                        .$fontstart2.$page->answerdata->response.$fontend2, " ");
            }
            $table->data[] = array($page->answerdata->score, " ");
        } else {
            $table->data[] = array(get_string('didnotanswerquestion', 'lesson'), " ");
        }
        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo html_writer::table($table);
        echo html_writer::end_tag('div');
    }
} else {
    print_error('unknowaction');
}

echo $OUTPUT->footer();
