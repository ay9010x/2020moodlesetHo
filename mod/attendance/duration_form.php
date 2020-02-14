<?php



require_once($CFG->libdir.'/formslib.php');


class mod_attendance_duration_form extends moodleform {

    
    public function definition() {

        $mform    =& $this->_form;

        $cm            = $this->_customdata['cm'];
        $ids           = $this->_customdata['ids'];

        $mform->addElement('header', 'general', get_string('changeduration', 'attendance'));
        $mform->addElement('static', 'count', get_string('countofselected', 'attendance'), count(explode('_', $ids)));

        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i += 5) {
            $minutes[$i] = sprintf("%02d", $i);
        }
        $durselect[] =& $mform->createElement('select', 'hours', '', $hours);
        $durselect[] =& $mform->createElement('select', 'minutes', '', $minutes, false, true);
        $mform->addGroup($durselect, 'durtime', get_string('newduration', 'attendance'), array(' '), true);

        $mform->addElement('hidden', 'ids', $ids);
        $mform->setType('ids', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'id', $cm->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', mod_attendance_sessions_page_params::ACTION_CHANGE_DURATION);
        $mform->setType('action', PARAM_INT);

        $mform->setDefaults(array('durtime' => array('hours' => 0, 'minutes' => 0)));

        $submitstring = get_string('update', 'attendance');
        $this->add_action_buttons(true, $submitstring);
    }

}
