<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');


class mod_assign_confirm_submission_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;

        list($requiresubmissionstatement,
             $submissionstatement,
             $coursemoduleid,
             $data) = $this->_customdata;

        if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement', '', $submissionstatement);
            $mform->addRule('submissionstatement', get_string('required'), 'required', null, 'client');
        }

        $mform->addElement('static', 'confirmmessage', '', get_string('confirmsubmission', 'mod_assign'));
        $mform->addElement('hidden', 'id', $coursemoduleid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'confirmsubmit');
        $mform->setType('action', PARAM_ALPHA);
        $this->add_action_buttons(true, get_string('continue'));

        if ($data) {
            $this->set_data($data);
        }
    }

}
