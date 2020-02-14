<?php



require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

$PAGE->set_url('/admin/tool/task/scheduledtasks.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$strheading = get_string('scheduledtasks', 'tool_task');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);

require_login();

require_capability('moodle/site:config', context_system::instance());

$renderer = $PAGE->get_renderer('tool_task');

$action = optional_param('action', '', PARAM_ALPHAEXT);
$taskname = optional_param('task', '', PARAM_RAW);
$task = null;
$mform = null;

if ($taskname) {
    $task = \core\task\manager::get_scheduled_task($taskname);
    if (!$task) {
        print_error('invaliddata');
    }
}

if ($action == 'edit') {
    $PAGE->navbar->add(get_string('edittaskschedule', 'tool_task', $task->get_name()));
}

if ($task) {
    $mform = new tool_task_edit_scheduled_task_form(null, $task);
}

if ($mform && ($mform->is_cancelled() || !empty($CFG->preventscheduledtaskchanges))) {
    redirect(new moodle_url('/admin/tool/task/scheduledtasks.php'));
} else if ($action == 'edit' && empty($CFG->preventscheduledtaskchanges)) {

    if ($data = $mform->get_data()) {


        if ($data->resettodefaults) {
            $defaulttask = \core\task\manager::get_default_scheduled_task($taskname);
            $task->set_minute($defaulttask->get_minute());
            $task->set_hour($defaulttask->get_hour());
            $task->set_month($defaulttask->get_month());
            $task->set_day_of_week($defaulttask->get_day_of_week());
            $task->set_day($defaulttask->get_day());
            $task->set_disabled($defaulttask->get_disabled());
            $task->set_customised(false);
        } else {
            $task->set_minute($data->minute);
            $task->set_hour($data->hour);
            $task->set_month($data->month);
            $task->set_day_of_week($data->dayofweek);
            $task->set_day($data->day);
            $task->set_disabled($data->disabled);
            $task->set_customised(true);
        }

        try {
            \core\task\manager::configure_scheduled_task($task);
            redirect($PAGE->url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
        } catch (Exception $e) {
            redirect($PAGE->url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
        }
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('edittaskschedule', 'tool_task', $task->get_name()));
        $mform->display();
        echo $OUTPUT->footer();
    }

} else {
    echo $OUTPUT->header();
    $tasks = core\task\manager::get_all_scheduled_tasks();
    echo $renderer->scheduled_tasks_table($tasks);
    echo $OUTPUT->footer();
}
