<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/locallib.php');



abstract class quiz_access_rule_base {
    
    protected $quiz;
    
    protected $quizobj;
    
    protected $timenow;

    
    public function __construct($quizobj, $timenow) {
        $this->quizobj = $quizobj;
        $this->quiz = $quizobj->get_quiz();
        $this->timenow = $timenow;
    }

    
    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {
        return null;
    }

    
    public function prevent_new_attempt($numprevattempts, $lastattempt) {
        return false;
    }

    
    public function prevent_access() {
        return false;
    }

    
    public function is_preflight_check_required($attemptid) {
        return false;
    }

    
    public function add_preflight_check_form_fields(mod_quiz_preflight_check_form $quizform,
            MoodleQuickForm $mform, $attemptid) {
            }

    
    public function validate_preflight_check($data, $files, $errors, $attemptid) {
        return $errors;
    }

    
    public function notify_preflight_check_passed($attemptid) {
            }

    
    public function current_attempt_finished() {
            }

    
    public function description() {
        return '';
    }

    
    public function is_finished($numprevattempts, $lastattempt) {
        return false;
    }

    
    public function end_time($attempt) {
        return false;
    }

    
    public function time_left_display($attempt, $timenow) {
        $endtime = $this->end_time($attempt);
        if ($endtime === false) {
            return false;
        }
        return $endtime - $timenow;
    }

    
    public function attempt_must_be_in_popup() {
        return false;
    }

    
    public function get_popup_options() {
        return array();
    }

    
    public function setup_attempt_page($page) {
            }

    
    public function get_superceded_rules() {
        return array();
    }

    
    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
            }

    
    public static function validate_settings_form_fields(array $errors,
            array $data, $files, mod_quiz_mod_form $quizform) {

        return $errors;
    }

    
    public static function get_browser_security_choices() {
        return array();
    }

    
    public static function save_settings($quiz) {
            }

    
    public static function delete_settings($quiz) {
            }

    
    public static function get_settings_sql($quizid) {
        return array('', '', array());
    }

    
    public static function get_extra_settings($quizid) {
        return array();
    }
}
