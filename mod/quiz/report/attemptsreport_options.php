<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');



class mod_quiz_attempts_report_options {

    
    public $mode;

    
    public $quiz;

    
    public $cm;

    
    public $course;

    
    protected static $statefields = array(
        'stateinprogress' => quiz_attempt::IN_PROGRESS,
        'stateoverdue'    => quiz_attempt::OVERDUE,
        'statefinished'   => quiz_attempt::FINISHED,
        'stateabandoned'  => quiz_attempt::ABANDONED,
    );

    
    public $attempts = quiz_attempts_report::ENROLLED_WITH;

    
    public $group = 0;

    
    public $states = array(quiz_attempt::IN_PROGRESS, quiz_attempt::OVERDUE,
            quiz_attempt::FINISHED, quiz_attempt::ABANDONED);

    
    public $onlygraded = false;

    
    public $pagesize = quiz_attempts_report::DEFAULT_PAGE_SIZE;

    
    public $download = '';

    
    public $usercanseegrades;

    
    public $checkboxcolumn = false;

    
    public function __construct($mode, $quiz, $cm, $course) {
        $this->mode   = $mode;
        $this->quiz   = $quiz;
        $this->cm     = $cm;
        $this->course = $course;

        $this->usercanseegrades = quiz_report_should_show_grades($quiz, context_module::instance($cm->id));
    }

    
    protected function get_url_params() {
        $params = array(
            'id'         => $this->cm->id,
            'mode'       => $this->mode,
            'attempts'   => $this->attempts,
            'onlygraded' => $this->onlygraded,
        );

        if ($this->states) {
            $params['states'] = implode('-', $this->states);
        }

        if (groups_get_activity_groupmode($this->cm, $this->course)) {
            $params['group'] = $this->group;
        }
        return $params;
    }

    
    public function get_url() {
        return new moodle_url('/mod/quiz/report.php', $this->get_url_params());
    }

    
    public function process_settings_from_form($fromform) {
        $this->setup_from_form_data($fromform);
        $this->resolve_dependencies();
        $this->update_user_preferences();
    }

    
    public function process_settings_from_params() {
        $this->setup_from_user_preferences();
        $this->setup_from_params();
        $this->resolve_dependencies();
    }

    
    public function get_initial_form_data() {
        $toform = new stdClass();
        $toform->attempts   = $this->attempts;
        $toform->onlygraded = $this->onlygraded;
        $toform->pagesize   = $this->pagesize;

        if ($this->states) {
            foreach (self::$statefields as $field => $state) {
                $toform->$field = in_array($state, $this->states);
            }
        }

        return $toform;
    }

    
    public function setup_from_form_data($fromform) {
        $this->attempts   = $fromform->attempts;
        $this->group      = groups_get_activity_group($this->cm, true);
        $this->onlygraded = !empty($fromform->onlygraded);
        $this->pagesize   = $fromform->pagesize;

        $this->states = array();
        foreach (self::$statefields as $field => $state) {
            if (!empty($fromform->$field)) {
                $this->states[] = $state;
            }
        }
    }

    
    public function setup_from_params() {
        $this->attempts   = optional_param('attempts', $this->attempts, PARAM_ALPHAEXT);
        $this->group      = groups_get_activity_group($this->cm, true);
        $this->onlygraded = optional_param('onlygraded', $this->onlygraded, PARAM_BOOL);
        $this->pagesize   = optional_param('pagesize', $this->pagesize, PARAM_INT);

        $states = optional_param('states', '', PARAM_ALPHAEXT);
        if (!empty($states)) {
            $this->states = explode('-', $states);
        }

        $this->download   = optional_param('download', $this->download, PARAM_ALPHA);
    }

    
    public function setup_from_user_preferences() {
        $this->pagesize = get_user_preferences('quiz_report_pagesize', $this->pagesize);
    }

    
    public function update_user_preferences() {
        set_user_preference('quiz_report_pagesize', $this->pagesize);
    }

    
    public function resolve_dependencies() {
        if ($this->group) {
                        if ($this->attempts === null || $this->attempts == quiz_attempts_report::ALL_WITH) {
                $this->attempts = quiz_attempts_report::ENROLLED_WITH;
            }

        } else if (!$this->group && $this->course->id == SITEID) {
                        $this->attempts = quiz_attempts_report::ALL_WITH;

        } else if (!in_array($this->attempts, array(quiz_attempts_report::ALL_WITH, quiz_attempts_report::ENROLLED_WITH,
                quiz_attempts_report::ENROLLED_WITHOUT, quiz_attempts_report::ENROLLED_ALL))) {
            $this->attempts = quiz_attempts_report::ENROLLED_WITH;
        }

        $cleanstates = array();
        foreach (self::$statefields as $state) {
            if (in_array($state, $this->states)) {
                $cleanstates[] = $state;
            }
        }
        $this->states = $cleanstates;
        if (count($this->states) == count(self::$statefields)) {
                                    $this->states = null;
        }

        if (!quiz_report_can_filter_only_graded($this->quiz)) {
                                    $this->onlygraded = false;
        }

        if ($this->attempts == quiz_attempts_report::ENROLLED_WITHOUT) {
            $this->states = null;
            $this->onlygraded = false;
        }

        if (!$this->is_showing_finished_attempts()) {
            $this->onlygraded = false;
        }

        if ($this->pagesize < 1) {
            $this->pagesize = quiz_attempts_report::DEFAULT_PAGE_SIZE;
        }
    }

    
    protected function is_showing_finished_attempts() {
        return $this->states === null || in_array(quiz_attempt::FINISHED, $this->states);
    }
}
