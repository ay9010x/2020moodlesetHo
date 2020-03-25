<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();

use moodleform;

require_once($CFG->libdir . '/formslib.php');


class template_plans extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $options = array(
            'ajax' => 'tool_lp/form-user-selector',
            'multiple' => true,
            'data-capability' => 'moodle/competency:planmanage'
        );
        $mform->addElement('autocomplete', 'users', get_string('selectuserstocreateplansfor', 'tool_lp'), array(), $options);
        $mform->addElement('submit', 'submit', get_string('createplans', 'tool_lp'));
    }

}
