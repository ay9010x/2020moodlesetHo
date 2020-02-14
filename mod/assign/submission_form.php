<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');


class mod_assign_submission_form extends moodleform {

    
    public function definition() {
        global $USER;
        $mform = $this->_form;
        list($assign, $data) = $this->_customdata;
        $instance = $assign->get_instance();
        if ($instance->teamsubmission) {
            $submission = $assign->get_group_submission($data->userid, 0, true);
        } else {
            $submission = $assign->get_user_submission($data->userid, true);
        }
        if ($submission) {
            $mform->addElement('hidden', 'lastmodified', $submission->timemodified);
            $mform->setType('lastmodified', PARAM_INT);
        }

        $assign->add_submission_form_elements($mform, $data);
        $this->add_action_buttons(true, get_string('savechanges', 'assign'));
        if ($data) {
            $this->set_data($data);
        }
    }
}

