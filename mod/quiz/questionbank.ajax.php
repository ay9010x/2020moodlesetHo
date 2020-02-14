<?php




define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/question/editlib.php');

list($thispageurl, $contexts, $cmid, $cm, $quiz, $pagevars) =
        question_edit_setup('editq', '/mod/quiz/edit.php', true);

$course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
require_capability('mod/quiz:manage', $contexts->lowest());

$questionbank = new mod_quiz\question\bank\custom_view($contexts, $thispageurl, $course, $cm, $quiz);
$questionbank->set_quiz_has_attempts(quiz_has_attempts($quiz->id));

$output = $PAGE->get_renderer('mod_quiz', 'edit');
$contents = $output->question_bank_contents($questionbank, $pagevars);
echo json_encode(array(
    'status'   => 'OK',
    'contents' => $contents,
));
