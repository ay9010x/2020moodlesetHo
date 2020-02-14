<?php




require_once('../../config.php');
require_once('key_form.php');
require_once($CFG->dirroot.'/grade/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$id       = optional_param('id', 0, PARAM_INT); $delete   = optional_param('delete', 0, PARAM_BOOL);
$confirm  = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_url('/grade/export/key.php', array('id' => $id, 'courseid' => $courseid));

if ($id) {
    if (!$key = $DB->get_record('user_private_key', array('id' => $id))) {
        print_error('invalidgroupid');
    }
    if (empty($courseid)) {
        $courseid = $key->instance;

    } else if ($courseid != $key->instance) {
        print_error('invalidcourseid');
    }

    if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
        print_error('invalidcourseid');
    }

} else {
    if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
        print_error('invalidcourseid');
    }
    $key = new stdClass();
}

$key->courseid = $course->id;

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/grade:export', $context);

$plugins = grade_helper::get_plugins_export($course->id);
if (!isset($plugins['keymanager'])) {
    print_error('nopermissions');
}

if (!empty($key->userid) and $USER->id != $key->userid) {
    print_error('notownerofkey');
}

$returnurl = $CFG->wwwroot.'/grade/export/keymanager.php?id='.$course->id;

if ($id and $delete) {
    if (!$confirm) {
        $PAGE->set_title(get_string('deleteselectedkey'));
        $PAGE->set_heading($course->fullname);
        echo $OUTPUT->header();
        $optionsyes = array('id'=>$id, 'delete'=>1, 'courseid'=>$courseid, 'sesskey'=>sesskey(), 'confirm'=>1);
        $optionsno  = array('id'=>$courseid);
        $formcontinue = new single_button(new moodle_url('key.php', $optionsyes), get_string('yes'), 'get');
        $formcancel = new single_button(new moodle_url('keymanager.php', $optionsno), get_string('no'), 'get');
        echo $OUTPUT->confirm(get_string('deletekeyconfirm', 'userkey', $key->value), $formcontinue, $formcancel);
        echo $OUTPUT->footer();
        die;

    } else if (confirm_sesskey()){
        $DB->delete_records('user_private_key', array('id' => $id));
        redirect('keymanager.php?id='.$course->id);
    }
}

$editform = new key_form();
$editform->set_data($key);

if ($editform->is_cancelled()) {
    redirect($returnurl);

} elseif ($data = $editform->get_data()) {

    if ($data->id) {
        $record = new stdClass();
        $record->id            = $data->id;
        $record->iprestriction = $data->iprestriction;
        $record->validuntil    = $data->validuntil;
        $DB->update_record('user_private_key', $record);
    } else {
        create_user_key('grade/export', $USER->id, $course->id, $data->iprestriction, $data->validuntil);
    }

    redirect($returnurl);
}

$strkeys   = get_string('userkeys', 'userkey');
$strgrades = get_string('grades');

if ($id) {
    $strheading = get_string('edituserkey', 'userkey');
} else {
    $strheading = get_string('createuserkey', 'userkey');
}

$PAGE->navbar->add($strgrades, new moodle_url('/grade/index.php', array('id'=>$courseid)));
$PAGE->navbar->add($strkeys, new moodle_url('/grade/export/keymanager.php', array('id'=>$courseid)));
$PAGE->navbar->add($strheading);

$PAGE->set_title($strkeys);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

$editform->display();
echo $OUTPUT->footer();

