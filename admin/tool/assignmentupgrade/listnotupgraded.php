<?php



require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/locallib.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/upgradableassignmentstable.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/upgradableassignmentsbatchform.php');
require_once($CFG->dirroot . '/'.$CFG->admin.'/tool/assignmentupgrade/paginationform.php');

admin_externalpage_setup('assignmentupgrade', '', array(), tool_assignmentupgrade_url('listnotupgraded'));
$PAGE->navbar->add(get_string('listnotupgraded', 'tool_assignmentupgrade'));

$renderer = $PAGE->get_renderer('tool_assignmentupgrade');

$perpage = optional_param('perpage', 0, PARAM_INT);
if (!$perpage) {
    $perpage = get_user_preferences('tool_assignmentupgrade_perpage', 100);
} else {
    set_user_preference('tool_assignmentupgrade_perpage', $perpage);
}
$assignments = new tool_assignmentupgrade_assignments_table($perpage);

$batchform = new tool_assignmentupgrade_batchoperations_form();
$data = $batchform->get_data();

if ($data && $data->selectedassignments != '' || $data && isset($data->upgradeall)) {
    require_sesskey();
    echo $renderer->confirm_batch_operation_page($data);
} else {
    $paginationform = new tool_assignmentupgrade_pagination_form();
    $pagedata = new stdClass();
    $pagedata->perpage = $perpage;
    $paginationform->set_data($pagedata);
    echo $renderer->assignment_list_page($assignments, $batchform, $paginationform);
}

