<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class enrol_user_enrolment_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $user   = $this->_customdata['user'];
        $course = $this->_customdata['course'];
        $ue     = $this->_customdata['ue'];

        $mform->addElement('header','general', '');

        $options = array(ENROL_USER_ACTIVE    => get_string('participationactive', 'enrol'),
                         ENROL_USER_SUSPENDED => get_string('participationsuspended', 'enrol'));
        if (isset($options[$ue->status])) {
            $mform->addElement('select', 'status', get_string('participationstatus', 'enrol'), $options);
        }

        $mform->addElement('date_time_selector', 'timestart', get_string('enroltimestart', 'enrol'), array('optional' => true));

        $mform->addElement('date_time_selector', 'timeend', get_string('enroltimeend', 'enrol'), array('optional' => true));

        $mform->addElement('static', 'timecreated', get_string('enroltimecreated', 'enrol'), userdate($ue->timecreated));

        $mform->addElement('hidden', 'ue');
        $mform->setType('ue', PARAM_INT);

        $mform->addElement('hidden', 'ifilter');
        $mform->setType('ifilter', PARAM_ALPHA);

        $this->add_action_buttons();

        $this->set_data(array(
            'ue' => $ue->id,
            'status' => $ue->status,
            'timestart' => $ue->timestart,
            'timeend' => $ue->timeend
        ));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['timestart']) and !empty($data['timeend'])) {
            if ($data['timestart'] >= $data['timeend']) {
                $errors['timestart'] = get_string('error');
                $errors['timeend'] = get_string('error');
            }
        }

        return $errors;
    }
}
