<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


class tool_task_edit_scheduled_task_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        
        $task = $this->_customdata;

        $plugininfo = core_plugin_manager::instance()->get_plugin_info($task->get_component());
        $plugindisabled = $plugininfo && $plugininfo->is_enabled() === false && !$task->get_run_if_component_disabled();

        $lastrun = $task->get_last_run_time() ? userdate($task->get_last_run_time()) : get_string('never');
        $nextrun = $task->get_next_run_time();
        if ($plugindisabled) {
            $nextrun = get_string('plugindisabled', 'tool_task');
        } else if ($task->get_disabled()) {
            $nextrun = get_string('taskdisabled', 'tool_task');
        } else if ($nextrun > time()) {
            $nextrun = userdate($nextrun);
        } else {
            $nextrun = get_string('asap', 'tool_task');
        }
        $mform->addElement('static', 'lastrun', get_string('lastruntime', 'tool_task'), $lastrun);
        $mform->addElement('static', 'nextrun', get_string('nextruntime', 'tool_task'), $nextrun);

        $mform->addElement('text', 'minute', get_string('taskscheduleminute', 'tool_task'));
        $mform->setType('minute', PARAM_RAW);
        $mform->addHelpButton('minute', 'taskscheduleminute', 'tool_task');

        $mform->addElement('text', 'hour', get_string('taskschedulehour', 'tool_task'));
        $mform->setType('hour', PARAM_RAW);
        $mform->addHelpButton('hour', 'taskschedulehour', 'tool_task');

        $mform->addElement('text', 'day', get_string('taskscheduleday', 'tool_task'));
        $mform->setType('day', PARAM_RAW);
        $mform->addHelpButton('day', 'taskscheduleday', 'tool_task');

        $mform->addElement('text', 'month', get_string('taskschedulemonth', 'tool_task'));
        $mform->setType('month', PARAM_RAW);
        $mform->addHelpButton('month', 'taskschedulemonth', 'tool_task');

        $mform->addElement('text', 'dayofweek', get_string('taskscheduledayofweek', 'tool_task'));
        $mform->setType('dayofweek', PARAM_RAW);
        $mform->addHelpButton('dayofweek', 'taskscheduledayofweek', 'tool_task');

        $mform->addElement('advcheckbox', 'disabled', get_string('disabled', 'tool_task'));
        $mform->addHelpButton('disabled', 'disabled', 'tool_task');

        $mform->addElement('advcheckbox', 'resettodefaults', get_string('resettasktodefaults', 'tool_task'));
        $mform->addHelpButton('resettodefaults', 'resettasktodefaults', 'tool_task');

        $mform->disabledIf('minute', 'resettodefaults', 'checked');
        $mform->disabledIf('hour', 'resettodefaults', 'checked');
        $mform->disabledIf('day', 'resettodefaults', 'checked');
        $mform->disabledIf('dayofweek', 'resettodefaults', 'checked');
        $mform->disabledIf('month', 'resettodefaults', 'checked');
        $mform->disabledIf('disabled', 'resettodefaults', 'checked');

        $mform->addElement('hidden', 'task', get_class($task));
        $mform->setType('task', PARAM_RAW);
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $this->add_action_buttons(true, get_string('savechanges'));

                $this->set_data(\core\task\manager::record_from_scheduled_task($task));
    }

    
    public function validation($data, $files) {
        $error = parent::validation($data, $files);
        $fields = array('minute', 'hour', 'day', 'month', 'dayofweek');
        foreach ($fields as $field) {
            if (!self::validate_fields($field, $data[$field])) {
                $error[$field] = get_string('invaliddata', 'core_error');
            }
        }
        return $error;
    }

    
    public static function validate_fields($field, $value) {
        switch ($field) {
            case 'minute' :
            case 'hour' :
                $regex = "/\A\*\z|\A[0-5]?[0-9]\z|\A\*\/[0-5]?[0-9]\z|\A[0-5]?[0-9](,[0-5]?[0-9])*\z|\A[0-5]?[0-9]-[0-5]?[0-9]\z/";
                break;
            case 'day':
                $regex = "/\A\*\z|\A([1-2]?[0-9]|3[0-1])\z|\A\*\/([1-2]?[0-9]|3[0-1])\z|";
                $regex .= "\A([1-2]?[0-9]|3[0-1])(,([1-2]?[0-9]|3[0-1]))*\z|\A([1-2]?[0-9]|3[0-1])-([1-2]?[0-9]|3[0-1])\z/";
                break;
            case 'month':
                $regex = "/\A\*\z|\A([0-9]|1[0-2])\z|\A\*\/([0-9]|1[0-2])\z|\A([0-9]|1[0-2])(,([0-9]|1[0-2]))*\z|";
                $regex .= "\A([0-9]|1[0-2])-([0-9]|1[0-2])\z/";
                break;
            case 'dayofweek':
                $regex = "/\A\*\z|\A[0-6]\z|\A\*\/[0-6]\z|\A[0-6](,[0-6])*\z|\A[0-6]-[0-6]\z/";
                break;
            default:
                return false;
        }
        return (bool)preg_match($regex, $value);
    }
}

