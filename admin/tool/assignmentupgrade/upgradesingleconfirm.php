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

$assignmentinfo = tool_assignmentupgrade_get_assignment($assignmentid);

echo $renderer->convert_assignment_are_you_sure($assignmentinfo);
