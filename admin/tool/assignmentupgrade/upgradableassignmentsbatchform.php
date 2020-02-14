<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');


class tool_assignmentupgrade_batchoperations_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

        $mform->addElement('header', 'general', get_string('batchoperations', 'tool_assignmentupgrade'));
                $mform->addElement('hidden', 'selectedassignments', '', array('class'=>'selectedassignments'));
        $mform->setType('selectedassignments', PARAM_SEQUENCE);

        $mform->addElement('submit', 'upgradeselected', get_string('upgradeselected', 'tool_assignmentupgrade'));
        $mform->addElement('submit', 'upgradeall', get_string('upgradeall', 'tool_assignmentupgrade'));
    }

}

