<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/accessrule/timelimit/rule.php');



class quizaccess_timelimit_testcase extends basic_testcase {
    public function test_time_limit_access_rule() {
        $quiz = new stdClass();
        $quiz->timelimit = 3600;
        $cm = new stdClass();
        $cm->id = 0;
        $quizobj = new quiz($quiz, $cm, null);
        $rule = new quizaccess_timelimit($quizobj, 10000);
        $attempt = new stdClass();

        $this->assertEquals($rule->description(),
            get_string('quiztimelimit', 'quizaccess_timelimit', format_time(3600)));

        $attempt->timestart = 10000;
        $attempt->preview = 0;
        $this->assertEquals($rule->end_time($attempt), 13600);
        $this->assertEquals($rule->time_left_display($attempt, 10000), 3600);
        $this->assertEquals($rule->time_left_display($attempt, 12000), 1600);
        $this->assertEquals($rule->time_left_display($attempt, 14000), -400);

        $this->assertFalse($rule->prevent_access());
        $this->assertFalse($rule->prevent_new_attempt(0, $attempt));
        $this->assertFalse($rule->is_finished(0, $attempt));
    }
}
