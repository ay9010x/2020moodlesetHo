<?php



namespace tool_cohortroles\form;
defined('MOODLE_INTERNAL') || die();

use moodleform;
use context_system;

require_once($CFG->libdir . '/formslib.php');


class assign_role_cohort extends moodleform {

    
    public function definition() {
        global $PAGE;

        $mform = $this->_form;
        $roles = get_roles_for_contextlevels(CONTEXT_USER);

        if (empty($roles)) {
            $output = $PAGE->get_renderer('tool_cohortroles');
            $warning = $output->notify_problem(get_string('noassignableroles', 'tool_cohortroles'));
            $mform->addElement('html', $warning);
            return;
        }

        $options = array(
            'ajax' => 'tool_lp/form-user-selector',
            'multiple' => true
        );
        $mform->addElement('autocomplete', 'userids', get_string('selectusers', 'tool_cohortroles'), array(), $options);
        $mform->addRule('userids', null, 'required');

        $names = role_get_names();
        $options = array();
        foreach ($roles as $idx => $roleid) {
            $options[$roleid] = $names[$roleid]->localname;
        }

        $mform->addElement('select', 'roleid', get_string('selectrole', 'tool_cohortroles'), $options);
        $mform->addRule('roleid', null, 'required');

        $context = context_system::instance();
        $options = array(
            'ajax' => 'tool_lp/form-cohort-selector',
            'multiple' => true,
            'data-contextid' => $context->id,
            'data-includes' => 'all'
        );
        $mform->addElement('autocomplete', 'cohortids', get_string('selectcohorts', 'tool_cohortroles'), array(), $options);
        $mform->addRule('cohortids', null, 'required');
        $mform->addElement('submit', 'submit', get_string('assign', 'tool_cohortroles'));
    }

}
