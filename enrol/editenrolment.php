<?php



require('../config.php');
require_once("$CFG->dirroot/enrol/locallib.php"); require_once("$CFG->dirroot/enrol/renderer.php"); require_once("$CFG->dirroot/enrol/editenrolment_form.php"); 
$ueid   = required_param('ue', PARAM_INT);
$filter = optional_param('ifilter', 0, PARAM_INT); 
$ue = $DB->get_record('user_enrolments', array('id' => $ueid), '*', MUST_EXIST);
$user = $DB->get_record('user', array('id'=>$ue->userid), '*', MUST_EXIST);
$instance = $DB->get_record('enrol', array('id'=>$ue->enrolid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);

$usersurl = new moodle_url('/enrol/users.php', array('id' => $course->id));

if (!$plugin = enrol_get_plugin($instance->enrol)) {
    redirect($usersurl);
}
if (!$plugin->allow_manage($instance)) {
    redirect($usersurl);
}

require_login($course);
require_capability('enrol/'.$instance->enrol.':manage', context_course::instance($course->id, MUST_EXIST));

$manager = new course_enrolment_manager($PAGE, $course, $filter);
$table = new course_enrolment_users_table($manager, $PAGE);

$returnurl = new moodle_url($usersurl, $manager->get_url_params()+$table->get_url_params());
$url = new moodle_url('/enrol/editenrolment.php', $returnurl->params());

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
navigation_node::override_active_url($usersurl);

$mform = new enrol_user_enrolment_form($url, array('user'=>$user, 'course'=>$course, 'ue'=>$ue));
$mform->set_data($PAGE->url->params());

if ($mform->is_cancelled()) {
    redirect($returnurl);

} else if ($data = $mform->get_data()) {
    if ($manager->edit_enrolment($ue, $data)) {
        redirect($returnurl);
    }
}

$fullname = fullname($user);
$title = get_string('editenrolment', 'core_enrol');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add($title);
$PAGE->navbar->add($fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($fullname);
$mform->display();
echo $OUTPUT->footer();
