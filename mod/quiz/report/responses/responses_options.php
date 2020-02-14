<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_options.php');



class quiz_responses_options extends mod_quiz_attempts_report_options {

    
    public $showqtext = false;

    
    public $showresponses = true;

    
    public $showright = false;

    
    public $whichtries = question_attempt::LAST_TRY;

    protected function get_url_params() {
        $params = parent::get_url_params();
        $params['qtext']      = $this->showqtext;
        $params['resp']       = $this->showresponses;
        $params['right']      = $this->showright;
        if (quiz_allows_multiple_tries($this->quiz)) {
            $params['whichtries'] = $this->whichtries;
        }
        return $params;
    }

    public function get_initial_form_data() {
        $toform = parent::get_initial_form_data();
        $toform->qtext      = $this->showqtext;
        $toform->resp       = $this->showresponses;
        $toform->right      = $this->showright;
        if (quiz_allows_multiple_tries($this->quiz)) {
            $toform->whichtries = $this->whichtries;
        }

        return $toform;
    }

    public function setup_from_form_data($fromform) {
        parent::setup_from_form_data($fromform);

        $this->showqtext     = $fromform->qtext;
        $this->showresponses = $fromform->resp;
        $this->showright     = $fromform->right;
        if (quiz_allows_multiple_tries($this->quiz)) {
            $this->whichtries = $fromform->whichtries;
        }
    }

    public function setup_from_params() {
        parent::setup_from_params();

        $this->showqtext     = optional_param('qtext', $this->showqtext,     PARAM_BOOL);
        $this->showresponses = optional_param('resp',  $this->showresponses, PARAM_BOOL);
        $this->showright     = optional_param('right', $this->showright,     PARAM_BOOL);
        if (quiz_allows_multiple_tries($this->quiz)) {
            $this->whichtries    = optional_param('whichtries', $this->whichtries, PARAM_ALPHA);
        }
    }

    public function setup_from_user_preferences() {
        parent::setup_from_user_preferences();

        $this->showqtext     = get_user_preferences('quiz_report_responses_qtext', $this->showqtext);
        $this->showresponses = get_user_preferences('quiz_report_responses_resp',  $this->showresponses);
        $this->showright     = get_user_preferences('quiz_report_responses_right', $this->showright);
        if (quiz_allows_multiple_tries($this->quiz)) {
            $this->whichtries    = get_user_preferences('quiz_report_responses_which_tries', $this->whichtries);
        }
    }

    public function update_user_preferences() {
        parent::update_user_preferences();

        set_user_preference('quiz_report_responses_qtext', $this->showqtext);
        set_user_preference('quiz_report_responses_resp',  $this->showresponses);
        set_user_preference('quiz_report_responses_right', $this->showright);
        if (quiz_allows_multiple_tries($this->quiz)) {
            set_user_preference('quiz_report_responses_which_tries', $this->whichtries);
        }
    }

    public function resolve_dependencies() {
        parent::resolve_dependencies();

        if (!$this->showqtext && !$this->showresponses && !$this->showright) {
                        $this->showresponses = true;
        }

                        $this->checkboxcolumn = has_capability('mod/quiz:deleteattempts', context_module::instance($this->cm->id))
                && ($this->attempts != quiz_attempts_report::ENROLLED_WITHOUT);
    }
}
