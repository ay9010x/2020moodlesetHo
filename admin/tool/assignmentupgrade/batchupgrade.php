<?php



define('NO_OUTPUT_BUFFERING', true);

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/locallib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/upgradableassignmentstable.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/upgradableassignmentsbatchform.php');

require_sesskey();

admin_externalpage_setup('assignmentupgrade', '', array(), tool_assignmentupgrade_url('batchupgrade'));

$PAGE->set_pagelayout('maintenance');
$PAGE->navbar->add(get_string('batchupgrade', 'tool_assignmentupgrade'));

$renderer = $PAGE->get_renderer('tool_assignmentupgrade');

$confirm = required_param('confirm', PARAM_BOOL);
if (!$confirm) {
    print_error('invalidrequest');
    die();
}
raise_memory_limit(MEMORY_EXTRA);
\core\session\manager::write_close();

echo $renderer->header();
echo $renderer->heading(get_string('batchupgrade', 'tool_assignmentupgrade'));

$current = 0;
if (optional_param('upgradeall', false, PARAM_BOOL)) {
    $assignmentids = tool_assignmentupgrade_load_all_upgradable_assignmentids();
} else {
    $assignmentids = explode(',', optional_param('selected', '', PARAM_TEXT));
}
$total = count($assignmentids);

foreach ($assignmentids as $assignmentid) {
    list($summary, $success, $log) = tool_assignmentupgrade_upgrade_assignment($assignmentid);
    $current += 1;
    $params = array('current'=>$current, 'total'=>$total);
    echo $renderer->heading(get_string('upgradeprogress', 'tool_assignmentupgrade', $params), 3);
    echo $renderer->convert_assignment_result($summary, $success, $log);
}

echo $renderer->continue_button(tool_assignmentupgrade_url('listnotupgraded'));
echo $renderer->footer();
