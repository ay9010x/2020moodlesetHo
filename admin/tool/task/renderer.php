<?php




defined('MOODLE_INTERNAL') || die();


class tool_task_renderer extends plugin_renderer_base {
    
    public function scheduled_tasks_table($tasks) {
        global $CFG;

        $table = new html_table();
        $table->head  = array(get_string('name'),
                              get_string('component', 'tool_task'),
                              get_string('edit'),
                              get_string('lastruntime', 'tool_task'),
                              get_string('nextruntime', 'tool_task'),
                              get_string('taskscheduleminute', 'tool_task'),
                              get_string('taskschedulehour', 'tool_task'),
                              get_string('taskscheduleday', 'tool_task'),
                              get_string('taskscheduledayofweek', 'tool_task'),
                              get_string('taskschedulemonth', 'tool_task'),
                              get_string('faildelay', 'tool_task'),
                              get_string('default', 'tool_task'));
        $table->attributes['class'] = 'admintable generaltable';
        $data = array();
        $yes = get_string('yes');
        $no = get_string('no');
        $never = get_string('never');
        $asap = get_string('asap', 'tool_task');
        $disabledstr = get_string('taskdisabled', 'tool_task');
        $plugindisabledstr = get_string('plugindisabled', 'tool_task');
        foreach ($tasks as $task) {
            $customised = $task->is_customised() ? $no : $yes;
            if (empty($CFG->preventscheduledtaskchanges)) {
                $configureurl = new moodle_url('/admin/tool/task/scheduledtasks.php', array('action'=>'edit', 'task' => get_class($task)));
                $editlink = $this->action_icon($configureurl, new pix_icon('t/edit', get_string('edittaskschedule', 'tool_task', $task->get_name())));
            } else {
                $editlink = $this->render(new pix_icon('t/locked', get_string('scheduledtaskchangesdisabled', 'tool_task')));
            }

            $namecell = new html_table_cell($task->get_name() . "\n" . html_writer::tag('span', '\\'.get_class($task), array('class' => 'task-class')));
            $namecell->header = true;

            $component = $task->get_component();
            $plugininfo = null;
            list($type, $plugin) = core_component::normalize_component($component);
            if ($type === 'core') {
                $componentcell = new html_table_cell(get_string('corecomponent', 'tool_task'));
            } else {
                if ($plugininfo = core_plugin_manager::instance()->get_plugin_info($component)) {
                    $plugininfo->init_display_name();
                    $componentcell = new html_table_cell($plugininfo->displayname);
                } else {
                    $componentcell = new html_table_cell($component);
                }
            }

            $lastrun = $task->get_last_run_time() ? userdate($task->get_last_run_time()) : $never;
            $nextrun = $task->get_next_run_time();
            $disabled = false;
            if ($plugininfo && $plugininfo->is_enabled() === false && !$task->get_run_if_component_disabled()) {
                $disabled = true;
                $nextrun = $plugindisabledstr;
            } else if ($task->get_disabled()) {
                $disabled = true;
                $nextrun = $disabledstr;
            } else if ($nextrun > time()) {
                $nextrun = userdate($nextrun);
            } else {
                $nextrun = $asap;
            }

            $row = new html_table_row(array(
                        $namecell,
                        $componentcell,
                        new html_table_cell($editlink),
                        new html_table_cell($lastrun),
                        new html_table_cell($nextrun),
                        new html_table_cell($task->get_minute()),
                        new html_table_cell($task->get_hour()),
                        new html_table_cell($task->get_day()),
                        new html_table_cell($task->get_day_of_week()),
                        new html_table_cell($task->get_month()),
                        new html_table_cell($task->get_fail_delay()),
                        new html_table_cell($customised)));

            if ($disabled) {
                $row->attributes['class'] = 'disabled';
            }
            $data[] = $row;
        }
        $table->data = $data;
        return html_writer::table($table);
    }
}
