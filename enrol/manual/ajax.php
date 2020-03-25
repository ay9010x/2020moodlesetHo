<?php



define('AJAX_SCRIPT', true);

require('../../config.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/enrol/manual/locallib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

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
    case 'getassignable':
        $default_roldid = get_config('enrol_manual', 'roleid');
        $rolesarray = array();
        $assignable = get_default_enrol_role_shortname($context, 'teacher');
        foreach ($assignable as $id => $role) {
                $rolesarray[] = array('id' => $id, 'name' => $role);
        }
        $outcome->response = $rolesarray;
        break;
    case 'searchusers':
        $enrolid = required_param('enrolid', PARAM_INT);
        $search = optional_param('search', '', PARAM_RAW);
           
      if (!empty($search)) {       // by YCJ
        $page = optional_param('page', 0, PARAM_INT);
        $addedenrollment = optional_param('enrolcount', 0, PARAM_INT);
        $perpage = optional_param('perpage', 25, PARAM_INT);
        $outcome->response = $manager->get_potential_users($enrolid, $search, $searchanywhere, $page, $perpage, $addedenrollment);
        $extrafields = get_extra_user_fields($context);
        $useroptions = array();
                if (has_capability('moodle/user:viewdetails', context_system::instance())) {
            $useroptions['courseid'] = SITEID;
        } else {
            $useroptions['link'] = false;
        }
        foreach ($outcome->response['users'] as &$user) {
            $user->picture = $OUTPUT->user_picture($user, $useroptions);
            $user->fullname = fullname($user);
            $fieldvalues = array();
            foreach ($extrafields as $field) {
                $fieldvalues[] = s($user->{$field});
                unset($user->{$field});
            }
            $user->extrafields = implode(', ', $fieldvalues);
        }
        $outcome->response['users'] = array_values($outcome->response['users']);
      } else {
        $outcome->response = array('totalusers' => 0, 'users' => '');   // give an empty of "get_potential_users" by YCJ
      }
        $outcome->success = true;
        break;
    case 'searchcohorts':
        $enrolid = required_param('enrolid', PARAM_INT);
        $search = optional_param('search', '', PARAM_RAW);
        $page = optional_param('page', 0, PARAM_INT);
        $addedenrollment = optional_param('enrolcount', 0, PARAM_INT);
        $perpage = optional_param('perpage', 25, PARAM_INT);
        $outcome->response = enrol_manual_get_potential_cohorts($context, $enrolid, $search, $page, $perpage, $addedenrollment);
        $outcome->success = true;
        break;
    case 'enrol':
        $enrolid = required_param('enrolid', PARAM_INT);
        $cohort = $user = null;
        $cohortid = optional_param('cohortid', 0, PARAM_INT);
        if (!$cohortid) {
            $userid = required_param('userid', PARAM_INT);
            $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        } else {
            $cohort = $DB->get_record('cohort', array('id' => $cohortid), '*', MUST_EXIST);
            if (!cohort_can_view_cohort($cohort, $context)) {
                throw new enrol_ajax_exception('invalidenrolinstance');             }
        }

        $roleid = optional_param('role', null, PARAM_INT);
        $duration = optional_param('duration', 0, PARAM_FLOAT);
        $startdate = optional_param('startdate', 0, PARAM_INT);
        $recovergrades = optional_param('recovergrades', 0, PARAM_INT);

        if (empty($roleid)) {
            $roleid = null;
        }

        if (empty($startdate)) {
            if (!$startdate = get_config('enrol_manual', 'enrolstart')) {
                                $startdate = 4;
            }
        }

        switch($startdate) {
            case 2:
                $timestart = $course->startdate;
                break;
            case 4:
                $timestart = intval(substr(time(), 0, 8) . '00') - 1;
                break;
            case 3:
            default:
                $today = time();
                $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
                $timestart = $today;
                break;
        }
        if ($duration <= 0) {
            $timeend = 0;
        } else {
            $timeend = $timestart + intval($duration*24*60*60);
        }

        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins(true);
        if (!array_key_exists($enrolid, $instances)) {
            throw new enrol_ajax_exception('invalidenrolinstance');
        }
        $instance = $instances[$enrolid];
        if (!isset($plugins[$instance->enrol])) {
            throw new enrol_ajax_exception('enrolnotpermitted');
        }
        $plugin = $plugins[$instance->enrol];
        if ($plugin->allow_enrol($instance) && has_capability('enrol/'.$plugin->get_name().':enrol', $context)) {
            if ($user) {
                $plugin->enrol_user($instance, $user->id, $roleid, $timestart, $timeend, null, $recovergrades);
            } else {
                $plugin->enrol_cohort($instance, $cohort->id, $roleid, $timestart, $timeend, null, $recovergrades);
            }
        } else {
            throw new enrol_ajax_exception('enrolnotpermitted');
        }
        $outcome->success = true;
        break;

    default:
        throw new enrol_ajax_exception('unknowajaxaction');
}

echo json_encode($outcome);
