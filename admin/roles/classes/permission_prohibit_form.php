<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


class core_role_permission_prohibit_form extends moodleform {

    
    protected function definition() {
        global $CFG;

        $mform = $this->_form;
        list($context, $capability, $overridableroles) = $this->_customdata;

        list($needed, $forbidden) = get_roles_with_cap_in_context($context, $capability->name);
        foreach ($forbidden as $id => $unused) {
            unset($overridableroles[$id]);
        }

        $mform->addElement('header', 'ptohibitheader', get_string('roleprohibitheader', 'core_role'));

        $mform->addElement('select', 'roleid', get_string('roleselect', 'core_role'), $overridableroles);

        $mform->addElement('hidden', 'capability');
        $mform->setType('capability', PARAM_CAPABILITY);
        $mform->setDefault('capability', $capability->name);

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setDefault('contextid', $context->id);

        $mform->addElement('hidden', 'prohibit');
        $mform->setType('prohibit', PARAM_INT);
        $mform->setDefault('prohibit', 1);

        $this->add_action_buttons(true, get_string('prohibit', 'core_role'));
    }
}
