<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');


class tool_assignmentupgrade_pagination_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

        $mform->addElement('header', 'general', get_string('assignmentsperpage', 'tool_assignmentupgrade'));
                $options = array(10=>'10', 20=>'20', 50=>'50', 100=>'100');
        $mform->addElement('select', 'perpage', get_string('assignmentsperpage', 'assign'), $options);

                $mform->addElement('hidden', 'action', 'saveoptions');
        $mform->setType('action', PARAM_ALPHA);

                $this->add_action_buttons(false, get_string('updatetable', 'tool_assignmentupgrade'));
    }
}

