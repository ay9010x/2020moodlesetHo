<?php

require_once($CFG->libdir.'/formslib.php');

class mod_attendance_student_attendance_form extends moodleform {
    public function definition() {
        global $CFG, $USER;

        $mform  =& $this->_form;

        $course = $this->_customdata['course'];
        $cm = $this->_customdata['cm'];
        $modcontext = $this->_customdata['modcontext'];
        $attforsession = $this->_customdata['session'];
        $attblock = $this->_customdata['attendance'];

        $statuses = $attblock->get_statuses();

        $mform->addElement('hidden', 'sessid', null);
        $mform->setType('sessid', PARAM_INT);
        $mform->setConstant('sessid', $attforsession->id);

        $mform->addElement('hidden', 'sesskey', null);
        $mform->setType('sesskey', PARAM_INT);
        $mform->setConstant('sesskey', sesskey());

                $sesstiontitle = userdate($attforsession->sessdate, get_string('strftimedate')).' '
                .userdate($attforsession->sessdate, get_string('strftimehm', 'mod_attendance'));

        $mform->addElement('header', 'session', $sesstiontitle);

                if (!empty($attforsession->description)) {
            $mform->addElement('html', $attforsession->description);
        }

                $radioarray = array();
        foreach ($statuses as $status) {
            $radioarray[] =& $mform->createElement('radio', 'status', '', $status->description, $status->id, array());
        }
                $mform->addGroup($radioarray, 'statusarray', $USER->firstname.' '.$USER->lastname.':', array(''), false);
        $mform->addRule('statusarray', get_string('attendancenotset', 'attendance'), 'required', '', 'client', false, false);

        $this->add_action_buttons();
    }
}