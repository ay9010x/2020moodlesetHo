<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


class enrol_instance_edit_form extends moodleform {

    
    public function definition() {
        global $DB;

        $mform = $this->_form;

        list($instance, $plugin, $context, $type, $returnurl) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_' . $type));

        $plugin->edit_instance_form($instance, $mform, $context);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_COMPONENT);
        $instance->type = $type;

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);
        $mform->setConstant('returnurl', $returnurl);

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        list($instance, $plugin, $context, $type) = $this->_customdata;

        $pluginerrors = $plugin->edit_instance_validation($data, $files, $instance, $context);

        $errors = array_merge($errors, $pluginerrors);

        return $errors;
    }

}
