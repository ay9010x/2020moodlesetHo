<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');



class mod_quiz_class_testcase extends basic_testcase {
    public function test_cannot_review_message() {
        $quiz = new stdClass();
        $quiz->reviewattempt = 0x10010;
        $quiz->timeclose = 0;
        $quiz->attempts = 0;

        $cm = new stdClass();
        $cm->id = 123;

        $quizobj = new quiz($quiz, $cm, new stdClass(), false);

        $this->assertEquals('',
            $quizobj->cannot_review_message(mod_quiz_display_options::DURING));
        $this->assertEquals('',
            $quizobj->cannot_review_message(mod_quiz_display_options::IMMEDIATELY_AFTER));
        $this->assertEquals(get_string('noreview', 'quiz'),
            $quizobj->cannot_review_message(mod_quiz_display_options::LATER_WHILE_OPEN));
        $this->assertEquals(get_string('noreview', 'quiz'),
            $quizobj->cannot_review_message(mod_quiz_display_options::AFTER_CLOSE));

        $closetime = time() + 10000;
        $quiz->timeclose = $closetime;
        $quizobj = new quiz($quiz, $cm, new stdClass(), false);

        $this->assertEquals(get_string('noreviewuntil', 'quiz', userdate($closetime)),
            $quizobj->cannot_review_message(mod_quiz_display_options::LATER_WHILE_OPEN));
    }
}
