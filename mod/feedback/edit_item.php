<?php



require_once("../../config.php");
require_once("lib.php");

feedback_init_feedback_session();

$itemid = optional_param('id', false, PARAM_INT);
if (!$itemid) {
    $cmid = required_param('cmid', PARAM_INT);
    $typ = required_param('typ', PARAM_ALPHA);
}

if ($itemid) {
    $item = $DB->get_record('feedback_item', array('id' => $itemid), '*', MUST_EXIST);
    list($course, $cm) = get_course_and_cm_from_instance($item->feedback, 'feedback');
    $url = new moodle_url('/mod/feedback/edit_item.php', array('id' => $itemid));
    $typ = $item->typ;
} else {
    $item = null;
    list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'feedback');
    $url = new moodle_url('/mod/feedback/edit_item.php', array('cmid' => $cm->id, 'typ' => $typ));
    $item = (object)['id' => null, 'position' => -1, 'typ' => $typ, 'options' => ''];
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/feedback:edititems', $context);
$feedback = $PAGE->activityrecord;

$editurl = new moodle_url('/mod/feedback/edit.php', array('id' => $cm->id));

$PAGE->set_url($url);

if (!$item->id && $typ === 'pagebreak') {
    require_sesskey();
    feedback_create_pagebreak($feedback->id);
    redirect($editurl->out(false));
    exit;
}

if (!$typ || !file_exists($CFG->dirroot.'/mod/feedback/item/'.$typ.'/lib.php')) {
    print_error('typemissing', 'feedback', $editurl->out(false));
}

require_once($CFG->dirroot.'/mod/feedback/item/'.$typ.'/lib.php');

$itemobj = feedback_get_item_class($typ);

$itemobj->build_editform($item, $feedback, $cm);

if ($itemobj->is_cancelled()) {
    redirect($editurl);
    exit;
}
if ($itemobj->get_data()) {
    if ($item = $itemobj->save_item()) {
        feedback_move_item($item, $item->position);
        redirect($editurl);
    }
}

$strfeedbacks = get_string("modulenameplural", "feedback");
$strfeedback  = get_string("modulename", "feedback");

navigation_node::override_active_url(new moodle_url('/mod/feedback/edit.php',
        array('id' => $cm->id, 'do_show' => 'edit')));
if ($item->id) {
    $PAGE->navbar->add(get_string('edit_item', 'feedback'));
} else {
    $PAGE->navbar->add(get_string('add_item', 'feedback'));
}
$PAGE->set_heading($course->fullname);
$PAGE->set_title($feedback->name);
echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($feedback->name));

$current_tab = 'edit';
$id = $cm->id;
require('tabs.php');

if (isset($error)) {
    echo $error;
}
$itemobj->show_editform();


echo $OUTPUT->footer();
