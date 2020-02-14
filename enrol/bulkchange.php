<?php



require('../config.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/users_forms.php");
require_once("$CFG->dirroot/enrol/renderer.php");
require_once("$CFG->dirroot/group/lib.php");

$id         = required_param('id', PARAM_INT); $bulkuserop = required_param('bulkuserop', PARAM_ALPHANUMEXT);
$userids    = required_param_array('bulkuser', PARAM_INT);
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);
$filter     = optional_param('ifilter', 0, PARAM_INT);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    redirect(new moodle_url('/'));
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);
$PAGE->set_pagelayout('admin');

$manager = new course_enrolment_manager($PAGE, $course, $filter);
$table = new course_enrolment_users_table($manager, $PAGE);
$returnurl = new moodle_url('/enrol/users.php', $table->get_combined_url_params());
$actionurl = new moodle_url('/enrol/bulkchange.php', $table->get_combined_url_params()+array('bulkuserop' => $bulkuserop));

$PAGE->set_url($actionurl);
navigation_node::override_active_url(new moodle_url('/enrol/users.php', array('id' => $id)));

$ops = $table->get_bulk_user_enrolment_operations();
if (!array_key_exists($bulkuserop, $ops)) {
    throw new moodle_exception('invalidbulkenrolop');
}
$operation = $ops[$bulkuserop];

$users = $manager->get_users_enrolments($userids);

$mform = $operation->get_form($actionurl, array('users' => $users));
if ($mform === false) {
    if ($operation->process($manager, $users, new stdClass)) {
        redirect($returnurl);
    } else {
        print_error('errorwithbulkoperation', 'enrol');
    }
}
if ($mform->is_cancelled()) {
    redirect($returnurl);
}
if ($mform->is_submitted() && $mform->is_validated() && confirm_sesskey()) {
    if ($operation->process($manager, $users, $mform->get_data())) {
        redirect($returnurl);
    }
}

$pagetitle = get_string('bulkuseroperation', 'enrol');

$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
echo $OUTPUT->header();
echo $OUTPUT->heading($operation->get_title());
$mform->display();
echo $OUTPUT->footer();