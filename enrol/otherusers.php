<?php



require('../config.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/renderer.php");
require_once("$CFG->dirroot/group/lib.php");

$id      = required_param('id', PARAM_INT); $action  = optional_param('action', '', PARAM_ALPHANUMEXT);
$filter  = optional_param('ifilter', 0, PARAM_INT);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('moodle/course:reviewotherusers', $context);

if ($course->id == SITEID) {
    redirect("$CFG->wwwroot/");
}

$PAGE->set_pagelayout('admin');

$manager = new course_enrolment_manager($PAGE, $course, $filter);
$table = new course_enrolment_other_users_table($manager, $PAGE);
$PAGE->set_url('/enrol/otherusers.php', $manager->get_url_params()+$table->get_url_params());
navigation_node::override_active_url(new moodle_url('/enrol/otherusers.php', array('id' => $id)));

$userdetails = array (
    'picture' => false,
    'userfullnamedisplay' => false,
    'firstname' => get_string('firstname'),
    'lastname' => get_string('lastname'),
);
$extrafields = get_extra_user_fields($context);
foreach ($extrafields as $field) {
    $userdetails[$field] = get_user_field_name($field);
}

$fields = array(
    'userdetails' => $userdetails,
    'lastaccess' => get_string('lastaccess'),
    'role' => get_string('roles', 'role')
);

if (!has_capability('moodle/course:viewhiddenuserfields', $context)) {
    $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
    if (isset($hiddenfields['lastaccess'])) {
        unset($fields['lastaccess']);
    }
}

$table->set_fields($fields, $OUTPUT);


$renderer = $PAGE->get_renderer('core_enrol');
$canassign = has_capability('moodle/role:assign', $manager->get_context());
$users = $manager->get_other_users_for_display($renderer, $PAGE->url, $table->sort, $table->sortdirection, $table->page, $table->perpage);
$assignableroles = $manager->get_assignable_roles(true);
foreach ($users as $userid=>&$user) {
    $user['picture'] = $OUTPUT->render($user['picture']);
    $user['role'] = $renderer->user_roles_and_actions($userid, $user['roles'], $assignableroles, $canassign, $PAGE->url);
}

$table->set_total_users($manager->get_total_other_users());
$table->set_users($users);

$PAGE->set_title($course->fullname.': '.get_string('totalotherusers', 'enrol', $manager->get_total_other_users()));
$PAGE->set_heading($PAGE->title);

echo $OUTPUT->header();
echo $renderer->render($table);
echo $OUTPUT->footer();