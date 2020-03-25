<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';

class edit_letter_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $num   = $this->_customdata['num'];
        $admin = $this->_customdata['admin'];

        $mform->addElement('header', 'gradeletters', get_string('gradeletters', 'grades'));

                if (!$admin) {
            $mform->addElement('checkbox', 'override', get_string('overridesitedefaultgradedisplaytype', 'grades'));
            $mform->addHelpButton('override', 'overridesitedefaultgradedisplaytype', 'grades');
        }

        $gradeletter       = get_string('gradeletter', 'grades');
        $gradeboundary     = get_string('gradeboundary', 'grades');

        for ($i=1; $i<$num+1; $i++) {
            $gradelettername = 'gradeletter'.$i;
            $gradeboundaryname = 'gradeboundary'.$i;

            $entry = array();
            $entry[] = $mform->createElement('text', $gradelettername, $gradeletter . " $i");
            $mform->setType($gradelettername, PARAM_TEXT);

            if (!$admin) {
                $mform->disabledIf($gradelettername, 'override', 'notchecked');
            }

            $entry[] = $mform->createElement('static', '', '', '&ge;');
            $entry[] = $mform->createElement('text', $gradeboundaryname, $gradeboundary." $i");
            $entry[] = $mform->createElement('static', '', '', '%');
            $mform->addGroup($entry, 'gradeentry'.$i, $gradeletter." $i", array(' '), false);

            $mform->setType($gradeboundaryname, PARAM_FLOAT);

            if (!$admin) {
                $mform->disabledIf($gradeboundaryname, 'override', 'notchecked');
            }
        }

        if ($num > 0) {
            $mform->addHelpButton('gradeentry1', 'gradeletter', 'grades');
        }

                $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

                $this->add_action_buttons(!$admin);
    }

}


