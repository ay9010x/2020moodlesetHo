<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/ipaddress/rule.php');



class quizaccess_ipaddress_testcase extends basic_testcase {
    public function test_ipaddress_access_rule() {
        $quiz = new stdClass();
        $attempt = new stdClass();
        $cm = new stdClass();
        $cm->id = 0;

                        $quiz->subnet = getremoteaddr(null);
        if (!empty($quiz->subnet)) {
            $quizobj = new quiz($quiz, $cm, null);
            $rule = new quizaccess_ipaddress($quizobj, 0);

            $this->assertFalse($rule->prevent_access());
            $this->assertFalse($rule->description());
            $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
            $this->assertFalse($rule->is_finished(0, $attempt));
            $this->assertFalse($rule->end_time($attempt));
            $this->assertFalse($rule->time_left_display($attempt, 0));
        }

        $quiz->subnet = '0.0.0.0';
        $quizobj = new quiz($quiz, $cm, null);
        $rule = new quizaccess_ipaddress($quizobj, 0);

        $this->assertNotEmpty($rule->prevent_access());
        $this->assertEmpty($rule->description());
        $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
        $this->assertFalse($rule->is_finished(0, $attempt));
        $this->assertFalse($rule->end_time($attempt));
        $this->assertFalse($rule->time_left_display($attempt, 0));
    }
}
