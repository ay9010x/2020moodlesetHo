<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/feedback/file/locallib.php');


class mod_assign_batch_set_allocatedmarker_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $params = $this->_customdata;

        $mform->addElement('header', 'general', get_string('batchsetallocatedmarker', 'assign', $params['userscount']));
        $mform->addElement('static', 'userslist', get_string('selectedusers', 'assign'), $params['usershtml']);

        $options = $params['markers'];
        $mform->addElement('select', 'allocatedmarker', get_string('allocatedmarker', 'assign'), $options);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'setbatchmarkingallocation');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'selectedusers');
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('savechanges'));

    }

}

