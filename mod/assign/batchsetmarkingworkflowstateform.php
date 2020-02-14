<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/feedback/file/locallib.php');


class mod_assign_batch_set_marking_workflow_state_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $params = $this->_customdata;
        $formheader = get_string('batchsetmarkingworkflowstateforusers', 'assign', $params['userscount']);

        $mform->addElement('header', 'general', $formheader);
        $mform->addElement('static', 'userslist', get_string('selectedusers', 'assign'), $params['usershtml']);

        $options = $params['markingworkflowstates'];
        $mform->addElement('select', 'markingworkflowstate', get_string('markingworkflowstate', 'assign'), $options);

                $mform->addElement('selectyesno', 'sendstudentnotifications', get_string('sendstudentnotifications', 'assign'));
        $mform->setDefault('sendstudentnotifications', 0);
        $mform->disabledIf('sendstudentnotifications', 'markingworkflowstate', 'neq', ASSIGN_MARKING_WORKFLOW_STATE_RELEASED);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'setbatchmarkingworkflowstate');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'selectedusers');
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('savechanges'));

    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

                                if (!empty($data['sendstudentnotifications']) && $data['markingworkflowstate'] != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
            $errors['sendstudentnotifications'] = get_string('studentnotificationworkflowstateerror', 'assign');
        }

        return $errors;
    }
}

