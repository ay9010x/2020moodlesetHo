<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/numattempts/rule.php');



class quizaccess_numattempts_testcase extends basic_testcase {
    public function test_num_attempts_access_rule() {
        $quiz = new stdClass();
        $quiz->attempts = 3;
        $cm = new stdClass();
        $cm->id = 0;
        $quizobj = new quiz($quiz, $cm, null);
        $rule = new quizaccess_numattempts($quizobj, 0);
        $attempt = new stdClass();

        $this->assertEquals($rule->description(),
            get_string('attemptsallowedn', 'quizaccess_numattempts', 3));

        $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
        $this->assertFalse($rule->prevent_new_attempt(2, $attempt));
        $this->assertEquals($rule->prevent_new_attempt(3, $attempt),
            get_string('nomoreattempts', 'quiz'));
        $this->assertEquals($rule->prevent_new_attempt(666, $attempt),
            get_string('nomoreattempts', 'quiz'));

        $this->assertFalse($rule->is_finished(0, $attempt));
        $this->assertFalse($rule->is_finished(2, $attempt));
        $this->assertTrue($rule->is_finished(3, $attempt));
        $this->assertTrue($rule->is_finished(666, $attempt));

        $this->assertFalse($rule->prevent_access());
        $this->assertFalse($rule->end_time($attempt));
        $this->assertFalse($rule->time_left_display($attempt, 0));
    }
}
