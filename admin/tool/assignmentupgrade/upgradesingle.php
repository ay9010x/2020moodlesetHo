<?php



require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/locallib.php');

require_sesskey();

$assignmentid = required_param('id', PARAM_INT);

admin_externalpage_setup('assignmentupgrade',
                         '',
                         array(),
                         tool_assignmentupgrade_url('upgradesingle', array('id' => $assignmentid)));

$PAGE->navbar->add(get_string('upgradesingle', 'tool_assignmentupgrade'));
$renderer = $PAGE->get_renderer('tool_assignmentupgrade');

$log = '';
list($summary, $success, $log) = tool_assignmentupgrade_upgrade_assignment($assignmentid);

echo $renderer->header();
echo $renderer->heading(get_string('conversioncomplete', 'tool_assignmentupgrade'));
echo $renderer->convert_assignment_result($summary, $success, $log);
echo $renderer->continue_button(tool_assignmentupgrade_url('listnotupgraded'));
echo $renderer->footer();
