<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/safebrowser/rule.php');



class quizaccess_safebrowser_testcase extends basic_testcase {
        public function test_safebrowser_access_rule() {
        $quiz = new stdClass();
        $quiz->browsersecurity = 'safebrowser';
        $cm = new stdClass();
        $cm->id = 0;
        $quizobj = new quiz($quiz, $cm, null);
        $rule = new quizaccess_safebrowser($quizobj, 0);
        $attempt = new stdClass();

                $_SERVER['HTTP_USER_AGENT'] = 'unknonw browser';
        $this->assertEquals(get_string('safebrowsererror', 'quizaccess_safebrowser'),
            $rule->prevent_access());

        $this->assertEquals(get_string('safebrowsernotice', 'quizaccess_safebrowser'),
            $rule->description());
        $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
        $this->assertFalse($rule->is_finished(0, $attempt));
        $this->assertFalse($rule->end_time($attempt));
        $this->assertFalse($rule->time_left_display($attempt, 0));
    }
}
