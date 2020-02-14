<?php



define('AJAX_SCRIPT', true);

require('../config.php');
require_once("$CFG->dirroot/enrol/locallib.php");
require_once("$CFG->dirroot/enrol/renderer.php");
require_once("$CFG->dirroot/group/lib.php");

$id      = required_param('id', PARAM_INT); $action  = required_param('action', PARAM_ALPHANUMEXT);

$PAGE->set_url(new moodle_url('/enrol/ajax.php', array('id'=>$id, 'action'=>$action)));

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

if ($course->id == SITEID) {
    throw new moodle_exception('invalidcourse');
}

require_login($course);
require_capability('moodle/course:enrolreview', $context);
require_sesskey();

echo $OUTPUT->header(); 
$manager = new course_enrolment_manager($PAGE, $course);

$outcome = new stdClass();
$outcome->success = true;
$outcome->response = new stdClass();
$outcome->error = '';

$searchanywhere = get_user_preferences('userselector_searchanywhere', false);

switch ($action) {
    case 'unenrol':
        $ue = $DB->get_record('user_enrolments', array('id'=>required_param('ue', PARAM_INT)), '*', MUST_EXIST);
        list ($instance, $plugin) = $manager->get_user_enrolment_components($ue);
        if (!$instance || !$plugin || !enrol_is_enabled($instance->enrol) || !$plugin->allow_unenrol_user($instance, $ue) || !has_capability("enrol/$instance->enrol:unenrol", $manager->get_context()) || !$manager->unenrol_user($ue)) {
            throw new enrol_ajax_exception('unenrolnotpermitted');
        }
        break;
    case 'unassign':
        $role = required_param('role', PARAM_INT);
        $user = required_param('user', PARAM_INT);
        if (!has_capability('moodle/role:assign', $manager->get_context()) || !$manager->unassign_role_from_user($user, $role)) {
            throw new enrol_ajax_exception('unassignnotpermitted');
        }
        break;
    case 'assign':
        $user = $DB->get_record('user', array('id'=>required_param('user', PARAM_INT)), '*', MUST_EXIST);
        $roleid = required_param('roleid', PARAM_INT);
        if (!array_key_exists($roleid, $manager->get_assignable_roles())) {
            throw new enrol_ajax_exception('invalidrole');
        }
        if (!has_capability('moodle/role:assign', $manager->get_context()) || !$manager->assign_role_to_user($roleid, $user->id)) {
            throw new enrol_ajax_exception('assignnotpermitted');
        }
        $outcome->response->roleid = $roleid;
        break;
    case 'getassignable':
        $otheruserroles = optional_param('otherusers', false, PARAM_BOOL);
        $outcome->response = $manager->get_assignable_roles_for_json($otheruserroles);
        break;
    case 'searchotherusers':
        $search = optional_param('search', '', PARAM_RAW);
        $page = optional_param('page', 0, PARAM_INT);
        $outcome->response = $manager->search_other_users($search, $searchanywhere, $page);
        $extrafields = get_extra_user_fields($context);
        $useroptions = array();
                if (has_capability('moodle/user:viewdetails', context_system::instance())) {
            $useroptions['courseid'] = SITEID;
        } else {
            $useroptions['link'] = false;
        }
        foreach ($outcome->response['users'] as &$user) {
            $user->userId = $user->id;
            $user->picture = $OUTPUT->user_picture($user, $useroptions);
            $user->fullname = fullname($user);
            $fieldvalues = array();
            foreach ($extrafields as $field) {
                $fieldvalues[] = s($user->{$field});
                unset($user->{$field});
            }
            $user->extrafields = implode(', ', $fieldvalues);
            unset($user->id);
        }
                                $outcome->response['users'] = array_values($outcome->response['users']);
        $outcome->success = true;
        break;
    default:
        throw new enrol_ajax_exception('unknowajaxaction');
}

echo json_encode($outcome);
die();
