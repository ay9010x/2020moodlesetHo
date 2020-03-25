<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->dirroot.'/lib/formslib.php');

class import_outcomes_form extends moodleform {

    public function definition() {
        global $PAGE, $USER;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'action', 'upload');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'courseid', $PAGE->course->id);
        $mform->setType('courseid', PARAM_INT);

        $scope = array();
        if (($PAGE->course->id > 1) && has_capability('moodle/grade:manage', context_system::instance())) {
            $mform->addElement('radio', 'scope', get_string('importcustom', 'grades'), null, 'custom');
            $mform->addElement('radio', 'scope', get_string('importstandard', 'grades'), null, 'global');
            $mform->setDefault('scope', 'custom');
        }

        $mform->addElement('filepicker', 'userfile', get_string('importoutcomes', 'grades'));
        $mform->addRule('userfile', get_string('required'), 'required', null, 'server');
        $mform->addHelpButton('userfile', 'importoutcomes', 'grades');

        $mform->addElement('submit', 'save', get_string('uploadthisfile'));

    }
}


