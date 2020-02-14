<?php




require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');


$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('quiz', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$quiz = $DB->get_record('quiz', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, false, $cm);

$reportlist = quiz_report_list(context_module::instance($cm->id));
if (empty($reportlist) || $userid == $USER->id) {
                        redirect(new moodle_url('/mod/quiz/view.php', array('id' => $cm->id)));
}

if ($userid) {

        $attempts = quiz_get_user_attempts($quiz->id, $userid, 'finished');
    $attempt = null;
    switch ($quiz->grademethod) {
        case QUIZ_ATTEMPTFIRST:
            $attempt = reset($attempts);
            break;

        case QUIZ_ATTEMPTLAST:
        case QUIZ_GRADEAVERAGE:
            $attempt = end($attempts);
            break;

        case QUIZ_GRADEHIGHEST:
            $maxmark = 0;
            foreach ($attempts as $at) {
                                if ((float) $at->sumgrades >= $maxmark) {
                    $maxmark = $at->sumgrades;
                    $attempt = $at;
                }
            }
            break;
    }

        if ($attempt) {
        $attemptobj = new quiz_attempt($attempt, $quiz, $cm, $course, false);
        if ($attemptobj->is_review_allowed()) {
            redirect($attemptobj->review_url());
        }
    }

    }

redirect(new moodle_url('/mod/quiz/report.php', array(
        'id' => $cm->id, 'mode' => reset($reportlist))));
