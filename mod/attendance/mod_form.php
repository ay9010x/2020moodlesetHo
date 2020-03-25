<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');


class mod_attendance_mod_form extends moodleform_mod {

    
    public function definition() {
        $mform    =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setDefault('name', get_string('modulename', 'attendance'));

                $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements(true);
        $this->add_action_buttons();
    }
}
