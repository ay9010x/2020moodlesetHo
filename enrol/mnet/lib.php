<?php



defined('MOODLE_INTERNAL') || die();


class enrol_mnet_plugin extends enrol_plugin {

    
    public function get_instance_name($instance) {
        global $DB;

        if (empty($instance)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);

        } else if (empty($instance->name)) {
            $enrol = $this->get_name();
            if ($role = $DB->get_record('role', array('id'=>$instance->roleid))) {
                $role = role_get_name($role, context_course::instance($instance->courseid, IGNORE_MISSING));
            } else {
                $role = get_string('error');
            }
            if (empty($instance->customint1)) {
                $host = get_string('remotesubscribersall', 'enrol_mnet');
            } else {
                $host = $DB->get_field('mnet_host', 'name', array('id'=>$instance->customint1));
            }
            return get_string('pluginname', 'enrol_'.$enrol) . ' (' . format_string($host) . ' - ' . $role .')';

        } else {
            return format_string($instance->name);
        }
    }

    
    public function can_add_instance($courseid) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/mnet/service/enrol/locallib.php');

        $service = mnetservice_enrol::get_instance();
        if (!$service->is_available()) {
            return false;
        }
        $coursecontext = context_course::instance($courseid);
        if (!has_capability('moodle/course:enrolconfig', $coursecontext)) {
            return false;
        }
        $subscribers = $service->get_remote_subscribers();
        if (empty($subscribers)) {
            return false;
        }

        return true;
    }

    
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/mnet:config', $context);
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/mnet:config', $context);
    }

    
    protected function get_valid_hosts_options() {
        global $CFG;
        require_once($CFG->dirroot.'/mnet/service/enrol/locallib.php');

        $service = mnetservice_enrol::get_instance();

        $subscribers = $service->get_remote_subscribers();
        $hosts = array(0 => get_string('remotesubscribersall', 'enrol_mnet'));
        foreach ($subscribers as $hostid => $subscriber) {
            $hosts[$hostid] = $subscriber->appname.': '.$subscriber->hostname.' ('.$subscriber->hosturl.')';
        }
        return $hosts;
    }

    
    protected function get_valid_roles_options($context) {
        $roles = get_assignable_roles($context);
        return $roles;
    }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $CFG;

        $hosts = $this->get_valid_hosts_options();
        $mform->addElement('select', 'customint1', get_string('remotesubscriber', 'enrol_mnet'), $hosts);
        $mform->addHelpButton('customint1', 'remotesubscriber', 'enrol_mnet');
        $mform->addRule('customint1', get_string('required'), 'required', null, 'client');

        $roles = $this->get_valid_roles_options($context);
        $mform->addElement('select', 'roleid', get_string('roleforremoteusers', 'enrol_mnet'), $roles);
        $mform->addHelpButton('roleid', 'roleforremoteusers', 'enrol_mnet');
        $mform->addRule('roleid', get_string('required'), 'required', null, 'client');
        $mform->setDefault('roleid', $this->get_config('roleid'));

        $mform->addElement('text', 'name', get_string('instancename', 'enrol_mnet'));
        $mform->addHelpButton('name', 'instancename', 'enrol_mnet');
        $mform->setType('name', PARAM_TEXT);
    }

    
    public function use_standard_editing_ui() {
        return true;
    }

    
    public function edit_instance_validation($data, $files, $instance, $context) {
        global $DB;
        $errors = array();

        $validroles = array_keys($this->get_valid_roles_options($context));
        $validhosts = array_keys($this->get_valid_hosts_options());

        $params = array('enrol' => 'mnet', 'courseid' => $instance->courseid, 'customint1' => $data['customint1']);
        if ($DB->record_exists('enrol', $params)) {
            $errors['customint1'] = get_string('error_multiplehost', 'enrol_mnet');
        }

        $tovalidate = array(
            'customint1' => $validhosts,
            'roleid' => $validroles,
            'name' => PARAM_TEXT
        );
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }
}
