<?php




require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/addrandomform.php');
require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/category_class.php');

list($thispageurl, $contexts, $cmid, $cm, $quiz, $pagevars) =
        question_edit_setup('editq', '/mod/quiz/addrandom.php', true);

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$addonpage = optional_param('addonpage', 0, PARAM_INT);
$category = optional_param('category', 0, PARAM_INT);
$scrollpos = optional_param('scrollpos', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $quiz->course))) {
    print_error('invalidcourseid');
}
require_capability('mod/quiz:manage', $contexts->lowest());
if (!$contexts->having_cap('moodle/question:useall')) {
    print_error('nopermissions', '', '', 'use');
}

$PAGE->set_url($thispageurl);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url('/mod/quiz/edit.php', array('cmid' => $cmid));
}
if ($scrollpos) {
    $returnurl->param('scrollpos', $scrollpos);
}

$defaultcategoryobj = question_make_default_categories($contexts->all());
$defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

$qcobject = new question_category_object(
    $pagevars['cpage'],
    $thispageurl,
    $contexts->having_one_edit_tab_cap('categories'),
    $defaultcategoryobj->id,
    $defaultcategory,
    null,
    $contexts->having_cap('moodle/question:add'));

$mform = new quiz_add_random_form(new moodle_url('/mod/quiz/addrandom.php'),
                array('contexts' => $contexts, 'cat' => $pagevars['cat']));

if ($mform->is_cancelled()) {
    redirect($returnurl);
}

if ($data = $mform->get_data()) {
    if (!empty($data->existingcategory)) {
        list($categoryid) = explode(',', $data->category);
        $includesubcategories = !empty($data->includesubcategories);
        $returnurl->param('cat', $data->category);

    } else if (!empty($data->newcategory)) {
        list($parentid, $contextid) = explode(',', $data->parent);
        $categoryid = $qcobject->add_category($data->parent, $data->name, '', true);
        $includesubcategories = 0;

        $returnurl->param('cat', $categoryid . ',' . $contextid);
    } else {
        throw new coding_exception(
                'It seems a form was submitted without any button being pressed???');
    }

    quiz_add_random_questions($quiz, $addonpage, $categoryid, $data->numbertoadd, $includesubcategories);
    quiz_delete_previews($quiz);
    quiz_update_sumgrades($quiz);
    redirect($returnurl);
}

$mform->set_data(array(
    'addonpage' => $addonpage,
    'returnurl' => $returnurl,
    'cmid' => $cm->id,
    'category' => $category,
));

$streditingquiz = get_string('editinga', 'moodle', get_string('modulename', 'quiz'));
$PAGE->navbar->add($streditingquiz);
$PAGE->set_title($streditingquiz);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

if (!$quizname = $DB->get_field($cm->modname, 'name', array('id' => $cm->instance))) {
            print_error('invalidcoursemodule');
}

echo $OUTPUT->heading(get_string('addrandomquestiontoquiz', 'quiz', $quizname), 2);
$mform->display();
echo $OUTPUT->footer();

