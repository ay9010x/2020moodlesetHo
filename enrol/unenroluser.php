<?php



require('../config.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/renderer.php");

$ueid    = required_param('ue', PARAM_INT); $confirm = optional_param('confirm', false, PARAM_BOOL);
$filter  = optional_param('ifilter', 0, PARAM_INT);

$ue = $DB->get_record('user_enrolments', array('id' => $ueid), '*', MUST_EXIST);
$user = $DB->get_record('user', array('id'=>$ue->userid), '*', MUST_EXIST);
$instance = $DB->get_record('enrol', array('id'=>$ue->enrolid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);

$context = context_course::instance($course->id);

$PAGE->set_url('/enrol/unenroluser.php', array('ue'=>$ueid, 'ifilter'=>$filter));

require_login($course);

if (!enrol_is_enabled($instance->enrol)) {
    print_error('erroreditenrolment', 'enrol');
}

$plugin = enrol_get_plugin($instance->enrol);

if (!$plugin->allow_unenrol_user($instance, $ue) or !has_capability("enrol/$instance->enrol:unenrol", $context)) {
    print_error('erroreditenrolment', 'enrol');
}

$manager = new course_enrolment_manager($PAGE, $course, $filter);
$table = new course_enrolment_users_table($manager, $PAGE);

$returnurl = new moodle_url('/enrol/users.php', array('id' => $course->id)+$manager->get_url_params()+$table->get_url_params());
$usersurl = new moodle_url('/enrol/users.php', array('id' => $course->id));

$PAGE->set_pagelayout('admin');
navigation_node::override_active_url($usersurl);

if ($confirm && confirm_sesskey()) {
    $plugin->unenrol_user($instance, $ue->userid);
    redirect($returnurl);
}

$yesurl = new moodle_url($PAGE->url, array('confirm'=>1, 'sesskey'=>sesskey()));
$message = get_string('unenrolconfirm', 'core_enrol', array('user'=>fullname($user, true), 'course'=>format_string($course->fullname)));
$fullname = fullname($user);
$title = get_string('unenrol', 'core_enrol');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->navbar->add($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($fullname);
echo $OUTPUT->confirm($message, $yesurl, $returnurl);
echo $OUTPUT->footer();
