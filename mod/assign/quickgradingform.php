<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');


class mod_assign_quick_grading_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

                $mform->addElement('html', $instance['gradingtable']);

                $mform->addElement('hidden', 'id', $instance['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'quickgrade');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'lastpage', $instance['page']);
        $mform->setType('lastpage', PARAM_INT);

                $mform->addElement('selectyesno', 'sendstudentnotifications', get_string('sendstudentnotifications', 'assign'));
        $mform->setDefault('sendstudentnotifications', $instance['sendstudentnotifications']);

                $savemessage = get_string('saveallquickgradingchanges', 'assign');
        $mform->addElement('submit', 'savequickgrades', $savemessage);
    }
}

