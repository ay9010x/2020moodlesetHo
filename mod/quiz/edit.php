<?php





require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/addrandomform.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/category_class.php');

$scrollpos = optional_param('scrollpos', '', PARAM_INT);

list($thispageurl, $contexts, $cmid, $cm, $quiz, $pagevars) =
        question_edit_setup('editq', '/mod/quiz/edit.php', true);

$defaultcategoryobj = question_make_default_categories($contexts->all());
$defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

$quizhasattempts = quiz_has_attempts($quiz->id);

$PAGE->set_url($thispageurl);

$course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
$quizobj = new quiz($quiz, $cm, $course);
$structure = $quizobj->get_structure();

require_capability('mod/quiz:manage', $contexts->lowest());

$params = array(
    'courseid' => $course->id,
    'context' => $contexts->lowest(),
    'other' => array(
        'quizid' => $quiz->id
    )
);
$event = \mod_quiz\event\edit_page_viewed::create($params);
$event->trigger();


$selectedslots = array();
$params = (array) data_submitted();
foreach ($params as $key => $value) {
    if (preg_match('!^s([0-9]+)$!', $key, $matches)) {
        $selectedslots[] = $matches[1];
    }
}

$afteractionurl = new moodle_url($thispageurl);
if ($scrollpos) {
    $afteractionurl->param('scrollpos', $scrollpos);
}

if (optional_param('repaginate', false, PARAM_BOOL) && confirm_sesskey()) {
        $structure->check_can_be_edited();
    $questionsperpage = optional_param('questionsperpage', $quiz->questionsperpage, PARAM_INT);
    quiz_repaginate_questions($quiz->id, $questionsperpage );
    quiz_delete_previews($quiz);
    redirect($afteractionurl);
}

if (($addquestion = optional_param('addquestion', 0, PARAM_INT)) && confirm_sesskey()) {
        $structure->check_can_be_edited();
    quiz_require_question_use($addquestion);
    $addonpage = optional_param('addonpage', 0, PARAM_INT);
    quiz_add_quiz_question($addquestion, $quiz, $addonpage);
    quiz_delete_previews($quiz);
    quiz_update_sumgrades($quiz);
    $thispageurl->param('lastchanged', $addquestion);
    redirect($afteractionurl);
}

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $structure->check_can_be_edited();
    $addonpage = optional_param('addonpage', 0, PARAM_INT);
        $rawdata = (array) data_submitted();
    foreach ($rawdata as $key => $value) {         if (preg_match('!^q([0-9]+)$!', $key, $matches)) {
            $key = $matches[1];
            quiz_require_question_use($key);
            quiz_add_quiz_question($key, $quiz, $addonpage);
        }
    }
    quiz_delete_previews($quiz);
    quiz_update_sumgrades($quiz);
    redirect($afteractionurl);
}

if ($addsectionatpage = optional_param('addsectionatpage', false, PARAM_INT)) {
        $structure->check_can_be_edited();
    $structure->add_section_heading($addsectionatpage);
    quiz_delete_previews($quiz);
    redirect($afteractionurl);
}

if ((optional_param('addrandom', false, PARAM_BOOL)) && confirm_sesskey()) {
        $structure->check_can_be_edited();
    $recurse = optional_param('recurse', 0, PARAM_BOOL);
    $addonpage = optional_param('addonpage', 0, PARAM_INT);
    $categoryid = required_param('categoryid', PARAM_INT);
    $randomcount = required_param('randomcount', PARAM_INT);
    quiz_add_random_questions($quiz, $addonpage, $categoryid, $randomcount, $recurse);

    quiz_delete_previews($quiz);
    quiz_update_sumgrades($quiz);
    redirect($afteractionurl);
}

if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {

        $maxgrade = unformat_float(optional_param('maxgrade', -1, PARAM_RAW));
    if ($maxgrade >= 0) {
        quiz_set_grade($maxgrade, $quiz);
        quiz_update_all_final_grades($quiz);
        quiz_update_grades($quiz, 0, true);
    }

    redirect($afteractionurl);
}

$questionbank = new mod_quiz\question\bank\custom_view($contexts, $thispageurl, $course, $cm, $quiz);
$questionbank->set_quiz_has_attempts($quizhasattempts);
$questionbank->process_actions($thispageurl, $cm);


$PAGE->set_pagelayout('incourse');
$PAGE->set_pagetype('mod-quiz-edit');

$output = $PAGE->get_renderer('mod_quiz', 'edit');

$PAGE->set_title(get_string('editingquizx', 'quiz', format_string($quiz->name)));
$PAGE->set_heading($course->fullname);
$node = $PAGE->settingsnav->find('mod_quiz_edit', navigation_node::TYPE_SETTING);
if ($node) {
    $node->make_active();
}
echo $OUTPUT->header();

$quizeditconfig = new stdClass();
$quizeditconfig->url = $thispageurl->out(true, array('qbanktool' => '0'));
$quizeditconfig->dialoglisteners = array();
$numberoflisteners = $DB->get_field_sql("
    SELECT COALESCE(MAX(page), 1)
      FROM {quiz_slots}
     WHERE quizid = ?", array($quiz->id));

for ($pageiter = 1; $pageiter <= $numberoflisteners; $pageiter++) {
    $quizeditconfig->dialoglisteners[] = 'addrandomdialoglaunch_' . $pageiter;
}

$PAGE->requires->data_for_js('quiz_edit_config', $quizeditconfig);
$PAGE->requires->js('/question/qengine.js');

echo html_writer::start_tag('div', array('class' => 'mod-quiz-edit-content'));

echo $output->edit_page($quizobj, $structure, $contexts, $thispageurl, $pagevars);

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
