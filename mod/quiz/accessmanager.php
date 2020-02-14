<?php




defined('MOODLE_INTERNAL') || die();



class quiz_access_manager {
    
    protected $quizobj;
    
    protected $timenow;
    
    protected $rules = array();

    
    public function __construct($quizobj, $timenow, $canignoretimelimits) {
        $this->quizobj = $quizobj;
        $this->timenow = $timenow;
        $this->rules = $this->make_rules($quizobj, $timenow, $canignoretimelimits);
    }

    
    protected function make_rules($quizobj, $timenow, $canignoretimelimits) {

        $rules = array();
        foreach (self::get_rule_classes() as $ruleclass) {
            $rule = $ruleclass::make($quizobj, $timenow, $canignoretimelimits);
            if ($rule) {
                $rules[$ruleclass] = $rule;
            }
        }

        $superceededrules = array();
        foreach ($rules as $rule) {
            $superceededrules += $rule->get_superceded_rules();
        }

        foreach ($superceededrules as $superceededrule) {
            unset($rules['quizaccess_' . $superceededrule]);
        }

        return $rules;
    }

    
    protected static function get_rule_classes() {
        return core_component::get_plugin_list_with_class('quizaccess', '', 'rule.php');
    }

    
    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {

        foreach (self::get_rule_classes() as $rule) {
            $rule::add_settings_form_fields($quizform, $mform);
        }
    }

    
    public static function get_browser_security_choices() {
        $options = array('-' => get_string('none', 'quiz'));
        foreach (self::get_rule_classes() as $rule) {
            $options += $rule::get_browser_security_choices();
        }
        return $options;
    }

    
    public static function validate_settings_form_fields(array $errors,
            array $data, $files, mod_quiz_mod_form $quizform) {

        foreach (self::get_rule_classes() as $rule) {
            $errors = $rule::validate_settings_form_fields($errors, $data, $files, $quizform);
        }

        return $errors;
    }

    
    public static function save_settings($quiz) {

        foreach (self::get_rule_classes() as $rule) {
            $rule::save_settings($quiz);
        }
    }

    
    public static function delete_settings($quiz) {

        foreach (self::get_rule_classes() as $rule) {
            $rule::delete_settings($quiz);
        }
    }

    
    protected static function get_load_sql($quizid, $rules, $basefields) {
        $allfields = $basefields;
        $alljoins = '{quiz} quiz';
        $allparams = array('quizid' => $quizid);

        foreach ($rules as $rule) {
            list($fields, $joins, $params) = $rule::get_settings_sql($quizid);
            if ($fields) {
                if ($allfields) {
                    $allfields .= ', ';
                }
                $allfields .= $fields;
            }
            if ($joins) {
                $alljoins .= ' ' . $joins;
            }
            if ($params) {
                $allparams += $params;
            }
        }

        if ($allfields === '') {
            return array('', array());
        }

        return array("SELECT $allfields FROM $alljoins WHERE quiz.id = :quizid", $allparams);
    }

    
    public static function load_settings($quizid) {
        global $DB;

        $rules = self::get_rule_classes();
        list($sql, $params) = self::get_load_sql($quizid, $rules, '');

        if ($sql) {
            $data = (array) $DB->get_record_sql($sql, $params);
        } else {
            $data = array();
        }

        foreach ($rules as $rule) {
            $data += $rule::get_extra_settings($quizid);
        }

        return $data;
    }

    
    public static function load_quiz_and_settings($quizid) {
        global $DB;

        $rules = self::get_rule_classes();
        list($sql, $params) = self::get_load_sql($quizid, $rules, 'quiz.*');
        $quiz = $DB->get_record_sql($sql, $params, MUST_EXIST);

        foreach ($rules as $rule) {
            foreach ($rule::get_extra_settings($quizid) as $name => $value) {
                $quiz->$name = $value;
            }
        }

        return $quiz;
    }

    
    public function get_active_rule_names() {
        $classnames = array();
        foreach ($this->rules as $rule) {
            $classnames[] = get_class($rule);
        }
        return $classnames;
    }

    
    protected function accumulate_messages($messages, $new) {
        if (is_array($new)) {
            $messages = array_merge($messages, $new);
        } else if (is_string($new) && $new) {
            $messages[] = $new;
        }
        return $messages;
    }

    
    public function describe_rules() {
        $result = array();
        foreach ($this->rules as $rule) {
            $result = $this->accumulate_messages($result, $rule->description());
        }
        return $result;
    }

    
    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        $reasons = array();
        foreach ($this->rules as $rule) {
            $reasons = $this->accumulate_messages($reasons,
                    $rule->prevent_new_attempt($numprevattempts, $lastattempt));
        }
        return $reasons;
    }

    
    public function prevent_access() {
        $reasons = array();
        foreach ($this->rules as $rule) {
            $reasons = $this->accumulate_messages($reasons, $rule->prevent_access());
        }
        return $reasons;
    }

    
    public function is_preflight_check_required($attemptid) {
        foreach ($this->rules as $rule) {
            if ($rule->is_preflight_check_required($attemptid)) {
                return true;
            }
        }
        return false;
    }

    
    public function get_preflight_check_form(moodle_url $url, $attemptid) {
                                $method = 'post';
        if (!empty($_GET['_qf__mod_quiz_preflight_check_form'])) {
            $method = 'get';
        }
        return new mod_quiz_preflight_check_form($url->out_omit_querystring(),
                array('rules' => $this->rules, 'quizobj' => $this->quizobj,
                      'attemptid' => $attemptid, 'hidden' => $url->params()), $method);
    }

    
    public function notify_preflight_check_passed($attemptid) {
        foreach ($this->rules as $rule) {
            $rule->notify_preflight_check_passed($attemptid);
        }
    }

    
    public function current_attempt_finished() {
        foreach ($this->rules as $rule) {
            $rule->current_attempt_finished();
        }
    }

    
    public function is_finished($numprevattempts, $lastattempt) {
        foreach ($this->rules as $rule) {
            if ($rule->is_finished($numprevattempts, $lastattempt)) {
                return true;
            }
        }
        return false;
    }

    
    public function setup_attempt_page($page) {
        foreach ($this->rules as $rule) {
            $rule->setup_attempt_page($page);
        }
    }

    
    public function get_end_time($attempt) {
        $timeclose = false;
        foreach ($this->rules as $rule) {
            $ruletimeclose = $rule->end_time($attempt);
            if ($ruletimeclose !== false && ($timeclose === false || $ruletimeclose < $timeclose)) {
                $timeclose = $ruletimeclose;
            }
        }
        return $timeclose;
    }

    
    public function get_time_left_display($attempt, $timenow) {
        $timeleft = false;
        foreach ($this->rules as $rule) {
            $ruletimeleft = $rule->time_left_display($attempt, $timenow);
            if ($ruletimeleft !== false && ($timeleft === false || $ruletimeleft < $timeleft)) {
                $timeleft = $ruletimeleft;
            }
        }
        return $timeleft;
    }

    
    public function attempt_must_be_in_popup() {
        foreach ($this->rules as $rule) {
            if ($rule->attempt_must_be_in_popup()) {
                return true;
            }
        }
        return false;
    }

    
    public function get_popup_options() {
        $options = array();
        foreach ($this->rules as $rule) {
            $options += $rule->get_popup_options();
        }
        return $options;
    }

    
    public function back_to_view_page($output, $message = '') {
        if ($this->attempt_must_be_in_popup()) {
            echo $output->close_attempt_popup($this->quizobj->view_url(), $message);
            die();
        } else {
            redirect($this->quizobj->view_url(), $message);
        }
    }

    
    public function make_review_link($attempt, $reviewoptions, $output) {

                if (in_array($attempt->state, array(quiz_attempt::IN_PROGRESS, quiz_attempt::OVERDUE))) {
            return $output->no_review_message('');
        }

        $when = quiz_attempt_state($this->quizobj->get_quiz(), $attempt);
        $reviewoptions = mod_quiz_display_options::make_from_quiz(
                $this->quizobj->get_quiz(), $when);

        if (!$reviewoptions->attempt) {
            return $output->no_review_message($this->quizobj->cannot_review_message($when, true));

        } else {
            return $output->review_link($this->quizobj->review_url($attempt->id),
                    $this->attempt_must_be_in_popup(), $this->get_popup_options());
        }
    }

    
    public function validate_preflight_check($data, $files, $attemptid) {
        $errors = array();
        foreach ($this->rules as $rule) {
            if ($rule->is_preflight_check_required($attemptid)) {
                $errors = $rule->validate_preflight_check($data, $files, $errors, $attemptid);
            }
        }
        return $errors;
    }
}
