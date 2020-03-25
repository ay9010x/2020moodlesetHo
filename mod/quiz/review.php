<?php




require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

$attemptid = required_param('attempt', PARAM_INT);
$page      = optional_param('page', 0, PARAM_INT);
$showall   = optional_param('showall', null, PARAM_BOOL);

$url = new moodle_url('/mod/quiz/review.php', array('attempt'=>$attemptid));
if ($page !== 0) {
    $url->param('page', $page);
} else if ($showall) {
    $url->param('showall', $showall);
}
$PAGE->set_url($url);

$attemptobj = quiz_attempt::create($attemptid);
$page = $attemptobj->force_page_number_into_range($page);

if ($showall === null) {
    $showall = $page == 0 && $attemptobj->get_default_show_all('review');
}
$PAGE->set_url($attemptobj->review_url(null, $page, $showall));

require_login($attemptobj->get_course(), false, $attemptobj->get_cm());
$attemptobj->check_review_capability();

$accessmanager = $attemptobj->get_access_manager(time());
$accessmanager->setup_attempt_page($PAGE);

$options = $attemptobj->get_display_options(true);

if ($attemptobj->is_own_attempt()) {
    if (!$attemptobj->is_finished()) {
        redirect($attemptobj->attempt_url(null, $page));

    } else if (!$options->attempt) {
        $accessmanager->back_to_view_page($PAGE->get_renderer('mod_quiz'),
                $attemptobj->cannot_review_message());
    }

} else if (!$attemptobj->is_review_allowed()) {
    throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noreviewattempt');
}

if ($showall) {
    $questionids = $attemptobj->get_slots();
} else {
    $questionids = $attemptobj->get_slots($page);
}

if ($options->flags == question_display_options::EDITABLE && optional_param('savingflags', false,
        PARAM_BOOL)) {
    require_sesskey();
    $attemptobj->save_question_flags();
    redirect($attemptobj->review_url(null, $page, $showall));
}

if ($attemptobj->is_own_preview()) {
    $strreviewtitle = get_string('reviewofpreview', 'quiz');
    navigation_node::override_active_url($attemptobj->start_attempt_url());

} else {
    $strreviewtitle = get_string('reviewofattempt', 'quiz', $attemptobj->get_attempt_number());
    if (empty($attemptobj->get_quiz()->showblocks) && !$attemptobj->is_preview_user()) {
        $PAGE->blocks->show_only_fake_blocks();
    }
}

$headtags = $attemptobj->get_html_head_contributions($page, $showall);
$PAGE->set_title($attemptobj->get_quiz_name());
$PAGE->set_heading($attemptobj->get_course()->fullname);


$attempt = $attemptobj->get_attempt();
$quiz = $attemptobj->get_quiz();
$overtime = 0;

if ($attempt->state == quiz_attempt::FINISHED) {
    if ($timetaken = ($attempt->timefinish - $attempt->timestart)) {
        if ($quiz->timelimit && $timetaken > ($quiz->timelimit + 60)) {
            $overtime = $timetaken - $quiz->timelimit;
            $overtime = format_time($overtime);
        }
        $timetaken = format_time($timetaken);
    } else {
        $timetaken = "-";
    }
} else {
    $timetaken = get_string('unfinished', 'quiz');
}

$summarydata = array();
if (!$attemptobj->get_quiz()->showuserpicture && $attemptobj->get_userid() != $USER->id) {
        $student = $DB->get_record('user', array('id' => $attemptobj->get_userid()));
    $userpicture = new user_picture($student);
    $userpicture->courseid = $attemptobj->get_courseid();
    $summarydata['user'] = array(
        'title'   => $userpicture,
        'content' => new action_link(new moodle_url('/user/view.php', array(
                                'id' => $student->id, 'course' => $attemptobj->get_courseid())),
                          fullname($student, true)),
    );
}

if ($attemptobj->has_capability('mod/quiz:viewreports')) {
    $attemptlist = $attemptobj->links_to_other_attempts($attemptobj->review_url(null, $page,
            $showall));
    if ($attemptlist) {
        $summarydata['attemptlist'] = array(
            'title'   => get_string('attempts', 'quiz'),
            'content' => $attemptlist,
        );
    }
}

$summarydata['startedon'] = array(
    'title'   => get_string('startedon', 'quiz'),
    'content' => userdate($attempt->timestart),
);

$summarydata['state'] = array(
    'title'   => get_string('attemptstate', 'quiz'),
    'content' => quiz_attempt::state_name($attempt->state),
);

if ($attempt->state == quiz_attempt::FINISHED) {
    $summarydata['completedon'] = array(
        'title'   => get_string('completedon', 'quiz'),
        'content' => userdate($attempt->timefinish),
    );
    $summarydata['timetaken'] = array(
        'title'   => get_string('timetaken', 'quiz'),
        'content' => $timetaken,
    );
}

if (!empty($overtime)) {
    $summarydata['overdue'] = array(
        'title'   => get_string('overdue', 'quiz'),
        'content' => $overtime,
    );
}

$grade = quiz_rescale_grade($attempt->sumgrades, $quiz, false);
if ($options->marks >= question_display_options::MARK_AND_MAX && quiz_has_grades($quiz)) {

    if ($attempt->state != quiz_attempt::FINISHED) {
        
    } else if (is_null($grade)) {
        $summarydata['grade'] = array(
            'title'   => get_string('grade', 'quiz'),
            'content' => quiz_format_grade($quiz, $grade),
        );

    } else {
                if ($quiz->grade != $quiz->sumgrades) {
            $a = new stdClass();
            $a->grade = quiz_format_grade($quiz, $attempt->sumgrades);
            $a->maxgrade = quiz_format_grade($quiz, $quiz->sumgrades);
            $summarydata['marks'] = array(
                'title'   => get_string('marks', 'quiz'),
                'content' => get_string('outofshort', 'quiz', $a),
            );
        }

                $a = new stdClass();
        $a->grade = html_writer::tag('b', quiz_format_grade($quiz, $grade));
        $a->maxgrade = quiz_format_grade($quiz, $quiz->grade);
        if ($quiz->grade != 100) {
            $a->percent = html_writer::tag('b', format_float(
                    $attempt->sumgrades * 100 / $quiz->sumgrades, 0));
            $formattedgrade = get_string('outofpercent', 'quiz', $a);
        } else {
            $formattedgrade = get_string('outof', 'quiz', $a);
        }
        $summarydata['grade'] = array(
            'title'   => get_string('grade', 'quiz'),
            'content' => $formattedgrade,
        );
    }
}

$summarydata = array_merge($summarydata, $attemptobj->get_additional_summary_data($options));

$feedback = $attemptobj->get_overall_feedback($grade);
if ($options->overallfeedback && $feedback) {
    $summarydata['feedback'] = array(
        'title'   => get_string('feedback', 'quiz'),
        'content' => $feedback,
    );
}


if ($showall) {
    $slots = $attemptobj->get_slots();
    $lastpage = true;
} else {
    $slots = $attemptobj->get_slots($page);
    $lastpage = $attemptobj->is_last_page($page);
}

$output = $PAGE->get_renderer('mod_quiz');

$navbc = $attemptobj->get_navigation_panel($output, 'quiz_review_nav_panel', $page, $showall);
$regions = $PAGE->blocks->get_regions();
$PAGE->blocks->add_fake_block($navbc, reset($regions));

echo $output->review_page($attemptobj, $slots, $page, $showall, $lastpage, $options, $summarydata);

$attemptobj->fire_attempt_reviewed_event();
