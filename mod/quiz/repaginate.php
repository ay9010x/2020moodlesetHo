<?php



require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$quizid = required_param('quizid', PARAM_INT);
$slotnumber = required_param('slot', PARAM_INT);
$repagtype = required_param('repag', PARAM_INT);

require_sesskey();
$quizobj = quiz::create($quizid);
require_login($quizobj->get_course(), false, $quizobj->get_cm());
require_capability('mod/quiz:manage', $quizobj->get_context());
if (quiz_has_attempts($quizid)) {
    $reportlink = quiz_attempt_summary_link_to_reports($quizobj->get_quiz(),
                    $quizobj->get_cm(), $quizobj->get_context());
    throw new \moodle_exception('cannoteditafterattempts', 'quiz',
            new moodle_url('/mod/quiz/edit.php', array('cmid' => $quizobj->get_cmid())), $reportlink);
}

$slotnumber++;
$repage = new \mod_quiz\repaginate($quizid);
$repage->repaginate_slots($slotnumber, $repagtype);

$structure = $quizobj->get_structure();
$slots = $structure->refresh_page_numbers_and_update_db();

redirect(new moodle_url('edit.php', array('cmid' => $quizobj->get_cmid())));
