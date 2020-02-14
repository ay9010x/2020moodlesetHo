<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');



class mod_quiz_preflight_check_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;
        $this->_form->updateAttributes(array('id' => 'mod_quiz_preflight_form'));

        foreach ($this->_customdata['hidden'] as $name => $value) {
            if ($name === 'sesskey') {
                continue;
            }
            $mform->addElement('hidden', $name, $value);
            $mform->setType($name, PARAM_INT);
        }

        foreach ($this->_customdata['rules'] as $rule) {
            if ($rule->is_preflight_check_required($this->_customdata['attemptid'])) {
                $rule->add_preflight_check_form_fields($this, $mform,
                        $this->_customdata['attemptid']);
            }
        }

        $this->add_action_buttons(true, get_string('startattempt', 'quiz'));
        $mform->setDisableShortforms();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $timenow = time();
        $accessmanager = $this->_customdata['quizobj']->get_access_manager($timenow);
        $errors = array_merge($errors, $accessmanager->validate_preflight_check($data, $files, $this->_customdata['attemptid']));

        return $errors;
    }
}
