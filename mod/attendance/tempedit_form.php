<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/formslib.php');

class tempedit_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('hidden', 'userid', 0);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'attheader', get_string('tempusersedit', 'attendance'));
        $mform->addElement('text', 'tname', get_string('tusername', 'attendance'));
        $mform->addRule('tname', 'Required', 'required', null, 'client');
        $mform->setType('tname', PARAM_TEXT);

        $mform->addElement('text', 'temail', get_string('tuseremail', 'attendance'));
        $mform->addRule('temail', 'Email', 'email', null, 'client');
        $mform->setType('temail', PARAM_EMAIL);

        $buttonarray = array(
            $mform->createElement('submit', 'submitbutton', get_string('edituser', 'attendance')),
            $mform->createElement('cancel'),
        );
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('submit');
    }

    public function definition_after_data() {
        $mform = $this->_form;
        $mform->applyFilter('tname', 'trim');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($err = mod_attendance_structure::check_existing_email($data['temail'], $data['userid'])) {
            $errors['temail'] = $err;
        }
        return $errors;
    }
}
