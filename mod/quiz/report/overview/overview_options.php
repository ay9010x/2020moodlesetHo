<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_options.php');



class quiz_overview_options extends mod_quiz_attempts_report_options {

    
    public $onlyregraded = false;

    
    public $slotmarks = true;

    protected function get_url_params() {
        $params = parent::get_url_params();
        $params['onlyregraded'] = $this->onlyregraded;
        $params['slotmarks']    = $this->slotmarks;
        return $params;
    }

    public function get_initial_form_data() {
        $toform = parent::get_initial_form_data();
        $toform->onlyregraded = $this->onlyregraded;
        $toform->slotmarks    = $this->slotmarks;

        return $toform;
    }

    public function setup_from_form_data($fromform) {
        parent::setup_from_form_data($fromform);

        $this->onlyregraded = !empty($fromform->onlyregraded);
        $this->slotmarks    = $fromform->slotmarks;
    }

    public function setup_from_params() {
        parent::setup_from_params();

        $this->onlyregraded = optional_param('onlyregraded', $this->onlyregraded, PARAM_BOOL);
        $this->slotmarks    = optional_param('slotmarks', $this->slotmarks, PARAM_BOOL);
    }

    public function setup_from_user_preferences() {
        parent::setup_from_user_preferences();

        $this->slotmarks = get_user_preferences('quiz_overview_slotmarks', $this->slotmarks);
    }

    public function update_user_preferences() {
        parent::update_user_preferences();

        if (quiz_has_grades($this->quiz)) {
            set_user_preference('quiz_overview_slotmarks', $this->slotmarks);
        }
    }

    public function resolve_dependencies() {
        parent::resolve_dependencies();

        if ($this->attempts == quiz_attempts_report::ENROLLED_WITHOUT) {
            $this->onlyregraded = false;
        }

        if (!$this->usercanseegrades) {
            $this->slotmarks = false;
        }

                        $this->checkboxcolumn = has_any_capability(
                array('mod/quiz:regrade', 'mod/quiz:deleteattempts'), context_module::instance($this->cm->id))
                && ($this->attempts != quiz_attempts_report::ENROLLED_WITHOUT);
    }
}
