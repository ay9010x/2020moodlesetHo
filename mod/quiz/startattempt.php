<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$id = required_param('cmid', PARAM_INT); $forcenew = optional_param('forcenew', false, PARAM_BOOL); $page = optional_param('page', -1, PARAM_INT); 
if (!$cm = get_coursemodule_from_id('quiz', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error("coursemisconf");
}

$quizobj = quiz::create($cm->instance, $USER->id);
$PAGE->set_url($quizobj->view_url());

require_login($quizobj->get_course(), false, $quizobj->get_cm());
require_sesskey();
$PAGE->set_heading($quizobj->get_course()->fullname);

if (!$quizobj->has_questions()) {
    if ($quizobj->has_capability('mod/quiz:manage')) {
        redirect($quizobj->edit_url());
    } else {
        print_error('cannotstartnoquestions', 'quiz', $quizobj->view_url());
    }
}

$timenow = time();
$accessmanager = $quizobj->get_access_manager($timenow);

list($currentattemptid, $attemptnumber, $lastattempt, $messages, $page) =
    quiz_validate_new_attempt($quizobj, $accessmanager, $forcenew, $page, true);

if (!$quizobj->is_preview_user() && $messages) {
    $output = $PAGE->get_renderer('mod_quiz');
    print_error('attempterror', 'quiz', $quizobj->view_url(),
            $output->access_messages($messages));
}

if ($accessmanager->is_preflight_check_required($currentattemptid)) {
        $mform = $accessmanager->get_preflight_check_form(
            $quizobj->start_attempt_url($page), $currentattemptid);

    if ($mform->is_cancelled()) {
        $accessmanager->back_to_view_page($PAGE->get_renderer('mod_quiz'));

    } else if (!$mform->get_data()) {

                $PAGE->set_url($quizobj->start_attempt_url($page));
        $PAGE->set_title($quizobj->get_quiz_name());
        $accessmanager->setup_attempt_page($PAGE);
        $output = $PAGE->get_renderer('mod_quiz');
        if (empty($quizobj->get_quiz()->showblocks)) {
            $PAGE->blocks->show_only_fake_blocks();
        }

        echo $output->start_attempt_page($quizobj, $mform);
        die();
    }

        $accessmanager->notify_preflight_check_passed($currentattemptid);
}
if ($currentattemptid) {
    if ($lastattempt->state == quiz_attempt::OVERDUE) {
        redirect($quizobj->summary_url($lastattempt->id));
    } else {
        redirect($quizobj->attempt_url($currentattemptid, $page));
    }
}

$attempt = quiz_prepare_and_start_new_attempt($quizobj, $attemptnumber, $lastattempt);

redirect($quizobj->attempt_url($attempt->id, $page));
