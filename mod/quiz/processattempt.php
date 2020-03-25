<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$timenow = time();

$attemptid     = required_param('attempt',  PARAM_INT);
$thispage      = optional_param('thispage', 0, PARAM_INT);
$nextpage      = optional_param('nextpage', 0, PARAM_INT);
$previous      = optional_param('previous',      false, PARAM_BOOL);
$next          = optional_param('next',          false, PARAM_BOOL);
$finishattempt = optional_param('finishattempt', false, PARAM_BOOL);
$timeup        = optional_param('timeup',        0,      PARAM_BOOL); $scrollpos     = optional_param('scrollpos',     '',     PARAM_RAW);

$attemptobj = quiz_attempt::create($attemptid);

if ($next) {
    $page = $nextpage;
} else if ($previous && $thispage > 0) {
    $page = $thispage - 1;
} else {
    $page = $thispage;
}
if ($page == -1) {
    $nexturl = $attemptobj->summary_url();
} else {
    $nexturl = $attemptobj->attempt_url(null, $page);
    if ($scrollpos !== '') {
        $nexturl->param('scrollpos', $scrollpos);
    }
}

require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
require_sesskey();

if ($attemptobj->get_userid() != $USER->id) {
    throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'notyourattempt');
}

if (!$attemptobj->is_preview_user()) {
    $attemptobj->require_capability('mod/quiz:attempt');
}

if ($attemptobj->is_finished()) {
    throw new moodle_quiz_exception($attemptobj->get_quizobj(),
            'attemptalreadyclosed', null, $attemptobj->review_url());
}

$status = $attemptobj->process_attempt($timenow, $finishattempt, $timeup, $thispage);

if ($status == quiz_attempt::OVERDUE) {
    redirect($attemptobj->summary_url());
} else if ($status == quiz_attempt::IN_PROGRESS) {
    redirect($nexturl);
} else {
        redirect($attemptobj->review_url());
}
