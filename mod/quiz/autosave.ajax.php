<?php



define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$timenow = time();
require_sesskey();

$attemptid = required_param('attempt',  PARAM_INT);
$thispage  = optional_param('thispage', 0, PARAM_INT);

$transaction = $DB->start_delegated_transaction();
$attemptobj = quiz_attempt::create($attemptid);

require_login($attemptobj->get_course(), false, $attemptobj->get_cm());

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

$attemptobj->process_auto_save($timenow);
$transaction->allow_commit();
echo 'OK';
