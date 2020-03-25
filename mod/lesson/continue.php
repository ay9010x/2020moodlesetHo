<?php





require_once("../../config.php");
require_once($CFG->dirroot.'/mod/lesson/locallib.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('lesson', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$lesson = new lesson($DB->get_record('lesson', array('id' => $cm->instance), '*', MUST_EXIST));

require_login($course, false, $cm);
require_sesskey();

$lesson->update_effective_access($USER->id);

$context = context_module::instance($cm->id);
$canmanage = has_capability('mod/lesson:manage', $context);
$lessonoutput = $PAGE->get_renderer('mod_lesson');

$url = new moodle_url('/mod/lesson/continue.php', array('id'=>$cm->id));
$PAGE->set_url($url);
$PAGE->set_pagetype('mod-lesson-view');
$PAGE->navbar->add(get_string('continue', 'lesson'));

if (!$canmanage) {
    $lesson->displayleft = lesson_displayleftif($lesson);
    $timer = $lesson->update_timer();
    if ($lesson->timelimit) {
        $timeleft = ($timer->starttime + $lesson->timelimit) - time();
        if ($timeleft <= 0) {
                        $lesson->add_message(get_string('eolstudentoutoftime', 'lesson'));
            redirect(new moodle_url('/mod/lesson/view.php', array('id'=>$cm->id,'pageid'=>LESSON_EOL, 'outoftime'=>'normal')));
        } else if ($timeleft < 60) {
                        $lesson->add_message(get_string("studentoneminwarning", "lesson"));
        }
    }
} else {
    $timer = new stdClass;
}

$page = $lesson->load_page(required_param('pageid', PARAM_INT));

$userhasgrade = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$USER->id));
$reviewmode = false;
if ($userhasgrade && !$lesson->retake) {
    $reviewmode = true;
}

if (count($page->answers) > 0) {
    $result = $page->record_attempt($context);
} else {
            $result = new stdClass;
    $result->newpageid       = optional_param('newpageid', $page->nextpageid, PARAM_INT);
    $result->nodefaultresponse  = true;
}

if (isset($USER->modattempts[$lesson->id])) {
        if ($USER->modattempts[$lesson->id]->pageid == $page->id && $page->nextpageid == 0) {          $result->newpageid = LESSON_EOL;
    } else {
        $nretakes = $DB->count_records("lesson_grades", array("lessonid"=>$lesson->id, "userid"=>$USER->id));
        $nretakes--;         $attempts = $DB->get_records("lesson_attempts", array("lessonid"=>$lesson->id, "userid"=>$USER->id, "retry"=>$nretakes), "timeseen", "id, pageid");
        $found = false;
        $temppageid = 0;
                $result->newpageid = LESSON_EOL;
        foreach($attempts as $attempt) {
            if ($found && $temppageid != $attempt->pageid) {                 $result->newpageid = $attempt->pageid;
                break;
            }
            if ($attempt->pageid == $page->id) {
                $found = true;                 $temppageid = $attempt->pageid;
            }
        }
    }
} elseif ($result->newpageid != LESSON_CLUSTERJUMP && $page->id != 0 && $result->newpageid > 0) {
            $newpage = $lesson->load_page($result->newpageid);
    if ($newpageid = $newpage->override_next_page($result->newpageid)) {
        $result->newpageid = $newpageid;
    }
} elseif ($result->newpageid == LESSON_UNSEENBRANCHPAGE) {
    if ($canmanage) {
        if ($page->nextpageid == 0) {
            $result->newpageid = LESSON_EOL;
        } else {
            $result->newpageid = $page->nextpageid;
        }
    } else {
        $result->newpageid = lesson_unseen_question_jump($lesson, $USER->id, $page->id);
    }
} elseif ($result->newpageid == LESSON_PREVIOUSPAGE) {
    $result->newpageid = $page->prevpageid;
} elseif ($result->newpageid == LESSON_RANDOMPAGE) {
    $result->newpageid = lesson_random_question_jump($lesson, $page->id);
} elseif ($result->newpageid == LESSON_CLUSTERJUMP) {
    if ($canmanage) {
        if ($page->nextpageid == 0) {              $result->newpageid = LESSON_EOL;
        } else {
            $result->newpageid = $page->nextpageid;
        }
    } else {
        $result->newpageid = $lesson->cluster_jump($page->id);
    }
}

if ($result->nodefaultresponse) {
        redirect(new moodle_url('/mod/lesson/view.php', array('id'=>$cm->id,'pageid'=>$result->newpageid)));
}


if ($canmanage) {
        if(lesson_display_teacher_warning($lesson)) {
        $warningvars = new stdClass();
        $warningvars->cluster = get_string("clusterjump", "lesson");
        $warningvars->unseen = get_string("unseenpageinbranch", "lesson");
        $lesson->add_message(get_string("teacherjumpwarning", "lesson", $warningvars));
    }
        if ($lesson->timelimit) {
        $lesson->add_message(get_string("teachertimerwarning", "lesson"));
    }
}
if ($result->attemptsremaining != 0 && $lesson->review && !$reviewmode) {
    $lesson->add_message(get_string('attemptsremaining', 'lesson', $result->attemptsremaining));
}

$PAGE->set_url('/mod/lesson/view.php', array('id' => $cm->id, 'pageid' => $page->id));
$PAGE->set_subpage($page->id);

lesson_add_fake_blocks($PAGE, $cm, $lesson, $timer);
echo $lessonoutput->header($lesson, $cm, 'view', true, $page->id, get_string('continue', 'lesson'));

if ($lesson->displayleft) {
    echo '<a name="maincontent" id="maincontent" title="'.get_string('anchortitle', 'lesson').'"></a>';
}
if ($lesson->ongoing && !$reviewmode) {
    echo $lessonoutput->ongoing_score($lesson);
}
if (!$reviewmode) {
    echo $result->feedback;
}

if (isset($USER->modattempts[$lesson->id])) {
    $url = $CFG->wwwroot.'/mod/lesson/view.php';
    $content = $OUTPUT->box(get_string("gotoendoflesson", "lesson"), 'center');
    $content .= $OUTPUT->box(get_string("or", "lesson"), 'center');
    $content .= $OUTPUT->box(get_string("continuetonextpage", "lesson"), 'center');
    $content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$cm->id));
    $content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'pageid', 'value'=>LESSON_EOL));
    $content .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'submit', 'value'=>get_string('finish', 'lesson')));
    echo html_writer::tag('form', "<div>$content</div>", array('method'=>'post', 'action'=>$url));
}

if (!$result->correctanswer && !$result->noanswer && !$result->isessayquestion && !$reviewmode && $lesson->review && !$result->maxattemptsreached) {
    $url = $CFG->wwwroot.'/mod/lesson/view.php';
    $content = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'id', 'value'=>$cm->id));
    $content .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'pageid', 'value'=>$page->id));
    $content .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'submit', 'value'=>get_string('reviewquestionback', 'lesson')));
    echo html_writer::tag('form', "<div class=\"singlebutton\">$content</div>", array('method'=>'post', 'action'=>$url));
}

$url = new moodle_url('/mod/lesson/view.php', array('id'=>$cm->id, 'pageid'=>$result->newpageid));
if ($lesson->review && !$result->correctanswer && !$result->noanswer && !$result->isessayquestion && !$result->maxattemptsreached) {
        echo $OUTPUT->single_button($url, get_string('reviewquestioncontinue', 'lesson'));
} else {
        echo $OUTPUT->single_button($url, get_string('continue', 'lesson'));
}

echo $lessonoutput->footer();
