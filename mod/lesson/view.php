<?php




require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/mod/lesson/locallib.php');
require_once($CFG->dirroot.'/mod/lesson/view_form.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/grade/constants.php');

$id      = required_param('id', PARAM_INT);             $pageid  = optional_param('pageid', null, PARAM_INT);   $edit    = optional_param('edit', -1, PARAM_BOOL);
$userpassword = optional_param('userpassword','',PARAM_RAW);
$backtocourse = optional_param('backtocourse', false, PARAM_RAW);

$cm = get_coursemodule_from_id('lesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$lesson = new lesson($DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST));

require_login($course, false, $cm);

if ($backtocourse) {
    redirect(new moodle_url('/course/view.php', array('id'=>$course->id)));
}

$lesson->update_effective_access($USER->id);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$url = new moodle_url('/mod/lesson/view.php', array('id'=>$id));
if ($pageid !== null) {
    $url->param('pageid', $pageid);
}
$PAGE->set_url($url);

$context = context_module::instance($cm->id);
$canmanage = has_capability('mod/lesson:manage', $context);

$lessonoutput = $PAGE->get_renderer('mod_lesson');

$reviewmode = false;
$userhasgrade = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$USER->id));
if ($userhasgrade && !$lesson->retake) {
    $reviewmode = true;
}

if (!$canmanage) {
    if (!$lesson->is_accessible()) {          echo $lessonoutput->header($lesson, $cm, '', false, null, get_string('notavailable'));
        if ($lesson->deadline != 0 && time() > $lesson->deadline) {
            echo $lessonoutput->lesson_inaccessible(get_string('lessonclosed', 'lesson', userdate($lesson->deadline)));
        } else {
            echo $lessonoutput->lesson_inaccessible(get_string('lessonopen', 'lesson', userdate($lesson->available)));
        }
        echo $lessonoutput->footer();
        exit();
    } else if ($lesson->usepassword && empty($USER->lessonloggedin[$lesson->id])) {         $correctpass = false;
        if (!empty($userpassword) && (($lesson->password == md5(trim($userpassword))) || ($lesson->password == trim($userpassword)))) {
            require_sesskey();
                        $correctpass = true;
            $USER->lessonloggedin[$lesson->id] = true;

        } else if (isset($lesson->extrapasswords)) {

                        foreach ($lesson->extrapasswords as $password) {
                if (strcmp($password, md5(trim($userpassword))) === 0 || strcmp($password, trim($userpassword)) === 0) {
                    require_sesskey();
                    $correctpass = true;
                    $USER->lessonloggedin[$lesson->id] = true;
                }
            }
        }
        if (!$correctpass) {
            echo $lessonoutput->header($lesson, $cm, '', false, null, get_string('passwordprotectedlesson', 'lesson', format_string($lesson->name)));
            echo $lessonoutput->login_prompt($lesson, $userpassword !== '');
            echo $lessonoutput->footer();
            exit();
        }
    } else if ($lesson->dependency) {         if ($dependentlesson = $DB->get_record('lesson', array('id' => $lesson->dependency))) {
                        $conditions = unserialize($lesson->conditions);
                        $errors = array();

                        if ($conditions->timespent) {
                $timespent = false;
                if ($attempttimes = $DB->get_records('lesson_timer', array("userid"=>$USER->id, "lessonid"=>$dependentlesson->id))) {
                                        foreach($attempttimes as $attempttime) {
                        $duration = $attempttime->lessontime - $attempttime->starttime;
                        if ($conditions->timespent < $duration/60) {
                            $timespent = true;
                        }
                    }
                }
                if (!$timespent) {
                    $errors[] = get_string('timespenterror', 'lesson', $conditions->timespent);
                }
            }

                        if($conditions->gradebetterthan) {
                $gradebetterthan = false;
                if ($studentgrades = $DB->get_records('lesson_grades', array("userid"=>$USER->id, "lessonid"=>$dependentlesson->id))) {
                                        foreach($studentgrades as $studentgrade) {
                        if ($studentgrade->grade >= $conditions->gradebetterthan) {
                            $gradebetterthan = true;
                        }
                    }
                }
                if (!$gradebetterthan) {
                    $errors[] = get_string('gradebetterthanerror', 'lesson', $conditions->gradebetterthan);
                }
            }

                        if ($conditions->completed) {
                if (!$DB->count_records('lesson_grades', array('userid'=>$USER->id, 'lessonid'=>$dependentlesson->id))) {
                    $errors[] = get_string('completederror', 'lesson');
                }
            }

            if (!empty($errors)) {                  echo $lessonoutput->header($lesson, $cm, '', false, null, get_string('completethefollowingconditions', 'lesson', format_string($lesson->name)));
                echo $lessonoutput->dependancy_errors($dependentlesson, $errors);
                echo $lessonoutput->footer();
                exit();
            }
        }
    }
}

    if ($pageid == LESSON_UNSEENBRANCHPAGE) {
    $pageid = lesson_unseen_question_jump($lesson, $USER->id, $pageid);
}

$attemptflag = false;
if (empty($pageid)) {
        if (!$DB->get_field('lesson_pages', 'id', array('lessonid' => $lesson->id, 'prevpageid' => 0))) {
        if (!$canmanage) {
            $lesson->add_message(get_string('lessonnotready2', 'lesson'));         } else {
            if (!$DB->count_records('lesson_pages', array('lessonid'=>$lesson->id))) {
                redirect("$CFG->wwwroot/mod/lesson/edit.php?id=$cm->id");             } else {
                $lesson->add_message(get_string('lessonpagelinkingbroken', 'lesson'));              }
        }
    }

        $retries = $DB->count_records('lesson_grades', array("lessonid" => $lesson->id, "userid" => $USER->id));
    if ($retries > 0) {
        $attemptflag = true;
    }

    if (isset($USER->modattempts[$lesson->id])) {
        unset($USER->modattempts[$lesson->id]);      }

        $allattempts = $lesson->get_attempts($retries);
    if (!empty($allattempts)) {
        $attempt = end($allattempts);
        $attemptpage = $lesson->load_page($attempt->pageid);
        $jumpto = $DB->get_field('lesson_answers', 'jumpto', array('id' => $attempt->answerid));
                if ($jumpto == 0) {
                        $nattempts = $lesson->get_attempts($attempt->retry, false, $attempt->pageid, $USER->id);
            if (count($nattempts) >= $lesson->maxattempts) {
                $lastpageseen = $lesson->get_next_page($attemptpage->nextpageid);
            } else {
                $lastpageseen = $attempt->pageid;
            }
        } elseif ($jumpto == LESSON_NEXTPAGE) {
            $lastpageseen = $lesson->get_next_page($attemptpage->nextpageid);
        } else if ($jumpto == LESSON_CLUSTERJUMP) {
            $lastpageseen = $lesson->cluster_jump($attempt->pageid);
        } else {
            $lastpageseen = $jumpto;
        }
    }

    if ($branchtables = $DB->get_records('lesson_branch', array("lessonid" => $lesson->id, "userid" => $USER->id, "retry" => $retries), 'timeseen DESC')) {
                $lastbranchtable = current($branchtables);
        if (count($allattempts) > 0) {
            if ($lastbranchtable->timeseen > $attempt->timeseen) {
                                if (!empty($lastbranchtable->nextpageid)) {
                    $lastpageseen = $lastbranchtable->nextpageid;
                } else {
                                        $lastpageseen = $lastbranchtable->pageid;
                }
            }
        } else {
                        if (!empty($lastbranchtable->nextpageid)) {
                $lastpageseen = $lastbranchtable->nextpageid;
            } else {
                                $lastpageseen = $lastbranchtable->pageid;
            }
        }
    }
        if ((isset($lastpageseen) && ($lastpageseen != LESSON_EOL))) {
        if (($DB->count_records('lesson_attempts', array('lessonid' => $lesson->id, 'userid' => $USER->id, 'retry' => $retries)) > 0)
                || $DB->count_records('lesson_branch', array("lessonid" => $lesson->id, "userid" => $USER->id, "retry" => $retries)) > 0) {

            echo $lessonoutput->header($lesson, $cm, '', false, null, get_string('leftduringtimedsession', 'lesson'));
            if ($lesson->timelimit) {
                if ($lesson->retake) {
                    $continuelink = new single_button(new moodle_url('/mod/lesson/view.php',
                            array('id' => $cm->id, 'pageid' => $lesson->firstpageid, 'startlastseen' => 'no')),
                            get_string('continue', 'lesson'), 'get');

                    echo html_writer::div($lessonoutput->message(get_string('leftduringtimed', 'lesson'), $continuelink),
                            'center leftduring');

                } else {
                    $courselink = new single_button(new moodle_url('/course/view.php',
                            array('id' => $PAGE->course->id)), get_string('returntocourse', 'lesson'), 'get');

                    echo html_writer::div($lessonoutput->message(get_string('leftduringtimednoretake', 'lesson'), $courselink),
                            'center leftduring');
                }
            } else {
                echo $lessonoutput->continue_links($lesson, $lastpageseen);
            }
            echo $lessonoutput->footer();
            exit();
        }
    }

    if ($attemptflag) {
        if (!$lesson->retake) {
            echo $lessonoutput->header($lesson, $cm, 'view', '', null, get_string("noretake", "lesson"));
            $courselink = new single_button(new moodle_url('/course/view.php', array('id'=>$PAGE->course->id)), get_string('returntocourse', 'lesson'), 'get');
            echo $lessonoutput->message(get_string("noretake", "lesson"), $courselink);
            echo $lessonoutput->footer();
            exit();
        }
    }
        if (!$pageid = $DB->get_field('lesson_pages', 'id', array('lessonid' => $lesson->id, 'prevpageid' => 0))) {
        echo $lessonoutput->header($lesson, $cm, 'view', '', null);
                        echo $lessonoutput->footer();
        exit();
    }
        if(!isset($USER->startlesson[$lesson->id]) && !$canmanage) {
        $lesson->start_timer();
    }
}

$currenttab = 'view';
$extraeditbuttons = false;
$lessonpageid = null;
$timer = null;

if ($pageid != LESSON_EOL) {
        $startlastseen = optional_param('startlastseen', '', PARAM_ALPHA);

    $page = $lesson->load_page($pageid);
        $newpageid = $page->callback_on_view($canmanage);
    if (is_numeric($newpageid)) {
        $page = $lesson->load_page($newpageid);
    }

        $event = \mod_lesson\event\course_module_viewed::create(array(
        'objectid' => $lesson->id,
        'context' => $context
    ));
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->trigger();

        
        if (!$canmanage) {
        $lesson->displayleft = lesson_displayleftif($lesson);

        $continue = ($startlastseen !== '');
        $restart  = ($continue && $startlastseen == 'yes');
        $timer = $lesson->update_timer($continue, $restart);

        if ($lesson->timelimit) {
            $timeleft = $timer->starttime + $lesson->timelimit - time();
            if ($timeleft <= 0) {
                                $lesson->add_message(get_string('eolstudentoutoftime', 'lesson'));
                redirect(new moodle_url('/mod/lesson/view.php', array('id'=>$cm->id,'pageid'=>LESSON_EOL, 'outoftime'=>'normal')));
                die;             } else if ($timeleft < 60) {
                                $lesson->add_message(get_string('studentoneminwarning', 'lesson'));
            }
        }

        if ($page->qtype == LESSON_PAGE_BRANCHTABLE && $lesson->minquestions) {
                        $ntries = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$USER->id));
            $gradeinfo = lesson_grade($lesson, $ntries);
            if ($gradeinfo->attempts) {
                if ($gradeinfo->nquestions < $lesson->minquestions) {
                    $a = new stdClass;
                    $a->nquestions   = $gradeinfo->nquestions;
                    $a->minquestions = $lesson->minquestions;
                    $lesson->add_message(get_string('numberofpagesviewednotice', 'lesson', $a));
                }

                if (!$reviewmode && !$lesson->retake){
                    $lesson->add_message(get_string("numberofcorrectanswers", "lesson", $gradeinfo->earned), 'notify');
                    if ($lesson->grade != GRADE_TYPE_NONE) {
                        $a = new stdClass;
                        $a->grade = number_format($gradeinfo->grade * $lesson->grade / 100, 1);
                        $a->total = $lesson->grade;
                        $lesson->add_message(get_string('yourcurrentgradeisoutof', 'lesson', $a), 'notify');
                    }
                }
            }
        }
    } else {
        $timer = null;
        if ($lesson->timelimit) {
            $lesson->add_message(get_string('teachertimerwarning', 'lesson'));
        }
        if (lesson_display_teacher_warning($lesson)) {
                                    $warningvars = new stdClass();
            $warningvars->cluster = get_string('clusterjump', 'lesson');
            $warningvars->unseen = get_string('unseenpageinbranch', 'lesson');
            $lesson->add_message(get_string('teacherjumpwarning', 'lesson', $warningvars));
        }
    }

    $PAGE->set_subpage($page->id);
    $currenttab = 'view';
    $extraeditbuttons = true;
    $lessonpageid = $page->id;
    $extrapagetitle = $page->title;

    if (($edit != -1) && $PAGE->user_allowed_editing()) {
        $USER->editing = $edit;
    }

    if (is_array($page->answers) && count($page->answers)>0) {
                        if (isset($USER->modattempts[$lesson->id])) {
            $retries = $DB->count_records('lesson_grades', array("lessonid"=>$lesson->id, "userid"=>$USER->id));
            if (!$attempts = $lesson->get_attempts($retries-1, false, $page->id)) {
                print_error('cannotfindpreattempt', 'lesson');
            }
            $attempt = end($attempts);
            $USER->modattempts[$lesson->id] = $attempt;
        } else {
            $attempt = false;
        }
        $lessoncontent = $lessonoutput->display_page($lesson, $page, $attempt);
    } else {
        $data = new stdClass;
        $data->id = $PAGE->cm->id;
        $data->pageid = $page->id;
        $data->newpageid = $lesson->get_next_page($page->nextpageid);

        $customdata = array(
            'title'     => $page->title,
            'contents'  => $page->get_contents()
        );
        $mform = new lesson_page_without_answers($CFG->wwwroot.'/mod/lesson/continue.php', $customdata);
        $mform->set_data($data);
        ob_start();
        $mform->display();
        $lessoncontent = ob_get_contents();
        ob_end_clean();
    }

    lesson_add_fake_blocks($PAGE, $cm, $lesson, $timer);
    echo $lessonoutput->header($lesson, $cm, $currenttab, $extraeditbuttons, $lessonpageid, $extrapagetitle);
    if ($attemptflag) {
                echo $OUTPUT->heading(get_string('attempt', 'lesson', $retries), 3);
    }
        if ($lesson->ongoing && !empty($pageid) && !$reviewmode) {
        echo $lessonoutput->ongoing_score($lesson);
    }
    if ($lesson->displayleft) {
        echo '<a name="maincontent" id="maincontent" title="' . get_string('anchortitle', 'lesson') . '"></a>';
    }
    echo $lessoncontent;
    echo $lessonoutput->progress_bar($lesson);
    echo $lessonoutput->footer();

} else {

    $lessoncontent = '';
            $outoftime = optional_param('outoftime', '', PARAM_ALPHA);

    $ntries = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$USER->id));
    if (isset($USER->modattempts[$lesson->id])) {
        $ntries--;      }
    $gradelesson = true;
    $gradeinfo = lesson_grade($lesson, $ntries);
    if ($lesson->custom && !$canmanage) {
                                                                if ($gradeinfo->nquestions < $lesson->minquestions) {
            $gradelesson = false;
            $a = new stdClass;
            $a->nquestions = $gradeinfo->nquestions;
            $a->minquestions = $lesson->minquestions;
            $lessoncontent .= $OUTPUT->box_start('generalbox boxaligncenter');
            $lesson->add_message(get_string('numberofpagesviewednotice', 'lesson', $a));
        }
    }
    if ($gradelesson) {
                $lessoncontent .= $OUTPUT->heading(get_string("congratulations", "lesson"), 3);
        $lessoncontent .= $OUTPUT->box_start('generalbox boxaligncenter');
    }
    if (!$canmanage) {
        if ($gradelesson) {
                        $progressbar = $lessonoutput->progress_bar($lesson);
                        $lesson->stop_timer();

                        $completion = new completion_info($course);
            if ($completion->is_enabled($cm) && $lesson->completionendreached) {
                $completion->update_state($cm, COMPLETION_COMPLETE);
            }

            if ($lesson->completiontimespent > 0) {
                $duration = $DB->get_field_sql(
                    "SELECT SUM(lessontime - starttime)
                                   FROM {lesson_timer}
                                  WHERE lessonid = :lessonid
                                    AND userid = :userid",
                    array('userid' => $USER->id, 'lessonid' => $lesson->id));
                if (!$duration) {
                    $duration = 0;
                }

                                if ($duration < $lesson->completiontimespent) {
                    $a = new stdClass;
                    $a->timespent = format_time($duration);
                    $a->timerequired = format_time($lesson->completiontimespent);
                    $lessoncontent .= $lessonoutput->paragraph(get_string("notenoughtimespent", "lesson", $a), 'center');
                }
            }


            if ($gradeinfo->attempts) {
                if (!$lesson->custom) {
                    $lessoncontent .= $lessonoutput->paragraph(get_string("numberofpagesviewed", "lesson", $gradeinfo->nquestions), 'center');
                    if ($lesson->minquestions) {
                        if ($gradeinfo->nquestions < $lesson->minquestions) {
                                                        $lessoncontent .= $lessonoutput->paragraph(get_string("youshouldview", "lesson", $lesson->minquestions), 'center');
                        }
                    }
                    $lessoncontent .= $lessonoutput->paragraph(get_string("numberofcorrectanswers", "lesson", $gradeinfo->earned), 'center');
                }
                $a = new stdClass;
                $a->score = $gradeinfo->earned;
                $a->grade = $gradeinfo->total;
                if ($gradeinfo->nmanual) {
                    $a->tempmaxgrade = $gradeinfo->total - $gradeinfo->manualpoints;
                    $a->essayquestions = $gradeinfo->nmanual;
                    $lessoncontent .= $OUTPUT->box(get_string("displayscorewithessays", "lesson", $a), 'center');
                } else {
                    $lessoncontent .= $OUTPUT->box(get_string("displayscorewithoutessays", "lesson", $a), 'center');
                }
                if ($lesson->grade != GRADE_TYPE_NONE) {
                    $a = new stdClass;
                    $a->grade = number_format($gradeinfo->grade * $lesson->grade / 100, 1);
                    $a->total = $lesson->grade;
                    $lessoncontent .= $lessonoutput->paragraph(get_string("yourcurrentgradeisoutof", "lesson", $a), 'center');
                }

                $grade = new stdClass();
                $grade->lessonid = $lesson->id;
                $grade->userid = $USER->id;
                $grade->grade = $gradeinfo->grade;
                $grade->completed = time();
                if (isset($USER->modattempts[$lesson->id])) {                     if (!$grades = $DB->get_records("lesson_grades",
                        array("lessonid" => $lesson->id, "userid" => $USER->id), "completed DESC", '*', 0, 1)) {
                        print_error('cannotfindgrade', 'lesson');
                    }
                    $oldgrade = array_shift($grades);
                    $grade->id = $oldgrade->id;
                    $DB->update_record("lesson_grades", $grade);
                } else {
                    $newgradeid = $DB->insert_record("lesson_grades", $grade);
                }
            } else {
                if ($lesson->timelimit) {
                    if ($outoftime == 'normal') {
                        $grade = new stdClass();
                        $grade->lessonid = $lesson->id;
                        $grade->userid = $USER->id;
                        $grade->grade = 0;
                        $grade->completed = time();
                        $newgradeid = $DB->insert_record("lesson_grades", $grade);
                        $lessoncontent .= $lessonoutput->paragraph(get_string("eolstudentoutoftimenoanswers", "lesson"));
                    }
                } else {
                    $lessoncontent .= $lessonoutput->paragraph(get_string("welldone", "lesson"));
                }
            }

                        lesson_update_grades($lesson, $USER->id);
            $lessoncontent .= $progressbar;
        }
    } else {
                if ($lesson->grade != GRADE_TYPE_NONE) {
            $lessoncontent .= $lessonoutput->paragraph(get_string("displayofgrade", "lesson"), 'center');
        }
    }
    $lessoncontent .= $OUTPUT->box_end(); 
    if ($lesson->modattempts && !$canmanage) {
                                        if (!$attempts = $lesson->get_attempts($ntries)) {
            $attempts = array();
            $url = new moodle_url('/mod/lesson/view.php', array('id'=>$PAGE->cm->id));
        } else {
            $firstattempt = current($attempts);
            $pageid = $firstattempt->pageid;
                                    $lastattempt = end($attempts);
            $USER->modattempts[$lesson->id] = $lastattempt->pageid;

            $url = new moodle_url('/mod/lesson/view.php', array('id'=>$PAGE->cm->id, 'pageid'=>$pageid));
        }
        $lessoncontent .= html_writer::link($url, get_string('reviewlesson', 'lesson'), array('class' => 'centerpadded lessonbutton standardbutton'));
    } elseif ($lesson->modattempts && $canmanage) {
        $lessoncontent .= $lessonoutput->paragraph(get_string("modattemptsnoteacher", "lesson"), 'centerpadded');
    }

    if ($lesson->activitylink) {
        $lessoncontent .= $lesson->link_for_activitylink();
    }

    $url = new moodle_url('/course/view.php', array('id'=>$course->id));
    $lessoncontent .= html_writer::link($url, get_string('returnto', 'lesson', format_string($course->fullname, true)), array('class'=>'centerpadded lessonbutton standardbutton'));

    if (has_capability('gradereport/user:view', context_course::instance($course->id))
            && $course->showgrades && $lesson->grade != 0 && !$lesson->practice) {
        $url = new moodle_url('/grade/index.php', array('id' => $course->id));
        $lessoncontent .= html_writer::link($url, get_string('viewgrades', 'lesson'),
            array('class' => 'centerpadded lessonbutton standardbutton'));
    }

    lesson_add_fake_blocks($PAGE, $cm, $lesson, $timer);
    echo $lessonoutput->header($lesson, $cm, $currenttab, $extraeditbuttons, $lessonpageid, get_string("congratulations", "lesson"));
    echo $lessoncontent;
    echo $lessonoutput->footer();
}
