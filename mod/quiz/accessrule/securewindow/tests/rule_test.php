<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/securewindow/rule.php');



class quizaccess_securewindow_testcase extends basic_testcase {
    public static $includecoverage = array('mod/quiz/accessrule/securewindow/rule.php');

        public function test_securewindow_access_rule() {
        $quiz = new stdClass();
        $quiz->browsersecurity = 'securewindow';
        $cm = new stdClass();
        $cm->id = 0;
        $quizobj = new quiz($quiz, $cm, null);
        $rule = new quizaccess_securewindow($quizobj, 0);
        $attempt = new stdClass();

        $this->assertFalse($rule->prevent_access());
        $this->assertEmpty($rule->description());
        $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
        $this->assertFalse($rule->is_finished(0, $attempt));
        $this->assertFalse($rule->end_time($attempt));
        $this->assertFalse($rule->time_left_display($attempt, 0));
    }
}
