<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');



class quizaccess_safebrowser extends quiz_access_rule_base {

    public static function make(quiz $quizobj, $timenow, $canignoretimelimits) {

        if ($quizobj->get_quiz()->browsersecurity !== 'safebrowser') {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    public function prevent_access() {
        if (!$this->check_safe_browser()) {
            return get_string('safebrowsererror', 'quizaccess_safebrowser');
        } else {
            return false;
        }
    }

    public function description() {
        return get_string('safebrowsernotice', 'quizaccess_safebrowser');
    }

    public function setup_attempt_page($page) {
        $page->set_title($this->quizobj->get_course()->shortname . ': ' . $page->title);
        $page->set_cacheable(false);
        $page->set_popup_notification_allowed(false);         $page->set_heading($page->title);
        $page->set_pagelayout('secure');
    }

    
    public function check_safe_browser() {
        return strpos($_SERVER['HTTP_USER_AGENT'], 'SEB') !== false;
    }

    
    public static function get_browser_security_choices() {
        global $CFG;

        if (empty($CFG->enablesafebrowserintegration)) {
            return array();
        }

        return array('safebrowser' =>
                get_string('requiresafeexambrowser', 'quizaccess_safebrowser'));
    }
}
