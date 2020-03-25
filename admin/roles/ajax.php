<?php


define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');

$contextid = required_param('contextid', PARAM_INT);
$getroles = optional_param('getroles', 0, PARAM_BOOL);

list($context, $course, $cm) = get_context_info_array($contextid);

$PAGE->set_context($context);

require_login($course, false, $cm);
require_capability('moodle/role:review', $context);
require_sesskey();

$OUTPUT->header();

list($overridableroles, $overridecounts, $nameswithcounts) = get_overridable_roles($context,
        ROLENAME_BOTH, true);

if ($getroles) {
    echo json_encode($overridableroles);
    die();
}

$capability = required_param('capability', PARAM_CAPABILITY);
$roleid = required_param('roleid', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

$capability = $DB->get_record('capabilities', array('name' => $capability), '*', MUST_EXIST);

if (!isset($overridableroles[$roleid])) {
    throw new moodle_exception('invalidarguments');
}

if (!has_capability('moodle/role:override', $context)) {
    if (!has_capability('moodle/role:safeoverride', $context) || !is_safe_capability($capability)) {
        require_capability('moodle/role:override', $context);
    }
}

switch ($action) {
    case 'allow':
        role_change_permission($roleid, $context, $capability->name, CAP_ALLOW);
        break;
    case 'prevent':
        role_change_permission($roleid, $context, $capability->name, CAP_PREVENT);
        break;
    case 'prohibit':
        role_change_permission($roleid, $context, $capability->name, CAP_PROHIBIT);
        break;
    case 'unprohibit':
        role_change_permission($roleid, $context, $capability->name, CAP_INHERIT);
        break;
    default:
        throw new moodle_exception('invalidarguments');
}

echo json_encode($action);
die();