<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');



class mod_quiz_attempt_testable extends quiz_attempt {
    
    protected $infos = array();

    
    public static function setup_fake_attempt_layout($id, $layout, $infos = array()) {
        $attempt = new stdClass();
        $attempt->id = $id;
        $attempt->layout = $layout;

        $course = new stdClass();
        $quiz = new stdClass();
        $cm = new stdClass();
        $cm->id = 0;

        $attemptobj = new self($attempt, $quiz, $cm, $course, false);

        $attemptobj->slots = array();
        foreach (explode(',', $layout) as $slot) {
            if ($slot == 0) {
                continue;
            }
            $attemptobj->slots[$slot] = new stdClass();
            $attemptobj->slots[$slot]->slot = $slot;
            $attemptobj->slots[$slot]->requireprevious = 0;
            $attemptobj->slots[$slot]->questionid = 0;
        }

        $attemptobj->sections = array();
        $attemptobj->sections[0] = new stdClass();
        $attemptobj->sections[0]->heading = '';
        $attemptobj->sections[0]->firstslot = 1;
        $attemptobj->sections[0]->shufflequestions = 0;

        $attemptobj->infos = $infos;
        $attemptobj->link_sections_and_slots();
        $attemptobj->determine_layout();
        $attemptobj->number_questions();

        return $attemptobj;
    }

    public function is_real_question($slot) {
        return !in_array($slot, $this->infos);
    }
}



class mod_quiz_attempt_testcase extends basic_testcase {
    
    public function test_attempt_url() {
        $attempt = mod_quiz_attempt_testable::setup_fake_attempt_layout(
                123, '1,2,0,3,4,0,5,6,0');

                $this->assertEquals(new moodle_url(
                '/mod/quiz/attempt.php?attempt=123'),
                $attempt->attempt_url());

        $this->assertEquals(new moodle_url(
                '/mod/quiz/attempt.php?attempt=123&page=2'),
                $attempt->attempt_url(null, 2));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/attempt.php?attempt=123&page=1#'),
                $attempt->attempt_url(3));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/attempt.php?attempt=123&page=1#q4'),
                $attempt->attempt_url(4));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->attempt_url(null, 2, 2));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->attempt_url(3, -1, 1));

        $this->assertEquals(new moodle_url(
                '#q4'),
                $attempt->attempt_url(4, -1, 1));

                $this->assertEquals(new moodle_url(
                '/mod/quiz/summary.php?attempt=123'),
                $attempt->summary_url());

                $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123'),
                $attempt->review_url());

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=2'),
                $attempt->review_url(null, 2));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=1'),
                $attempt->review_url(3, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=1#q4'),
                $attempt->review_url(4, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123'),
                $attempt->review_url(null, 2, true));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123'),
                $attempt->review_url(1, -1, true));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=2'),
                $attempt->review_url(null, 2, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&showall=0'),
                $attempt->review_url(null, 0, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&showall=0'),
                $attempt->review_url(1, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=1'),
                $attempt->review_url(3, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=2'),
                $attempt->review_url(null, 2));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(null, -1, null, 0));

        $this->assertEquals(new moodle_url(
                '#q3'),
                $attempt->review_url(3, -1, null, 0));

        $this->assertEquals(new moodle_url(
                '#q4'),
                $attempt->review_url(4, -1, null, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(null, 2, true, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(1, -1, true, 0));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=2'),
                $attempt->review_url(null, 2, false, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(null, 0, false, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(1, -1, false, 0));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=123&page=1#'),
                $attempt->review_url(3, -1, false, 0));

                $attempt = mod_quiz_attempt_testable::setup_fake_attempt_layout(
                124, '1,2,3,4,5,6,7,8,9,10,0,11,12,13,14,15,16,17,18,19,20,0,' .
                '21,22,23,24,25,26,27,28,29,30,0,31,32,33,34,35,36,37,38,39,40,0,' .
                '41,42,43,44,45,46,47,48,49,50,0,51,52,53,54,55,56,57,58,59,60,0');

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124'),
                $attempt->review_url());

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=2'),
                $attempt->review_url(null, 2));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=1'),
                $attempt->review_url(11, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=1#q12'),
                $attempt->review_url(12, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&showall=1'),
                $attempt->review_url(null, 2, true));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&showall=1'),
                $attempt->review_url(1, -1, true));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=2'),
                $attempt->review_url(null, 2, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124'),
                $attempt->review_url(null, 0, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=1'),
                $attempt->review_url(11, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=1#q12'),
                $attempt->review_url(12, -1, false));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=2'),
                $attempt->review_url(null, 2));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(null, -1, null, 0));

        $this->assertEquals(new moodle_url(
                '#q3'),
                $attempt->review_url(3, -1, null, 0));

        $this->assertEquals(new moodle_url(
                '#q4'),
                $attempt->review_url(4, -1, null, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(null, 2, true, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(1, -1, true, 0));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=2'),
                $attempt->review_url(null, 2, false, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(null, 0, false, 0));

        $this->assertEquals(new moodle_url(
                '#'),
                $attempt->review_url(1, -1, false, 0));

        $this->assertEquals(new moodle_url(
                '/mod/quiz/review.php?attempt=124&page=1#'),
                $attempt->review_url(11, -1, false, 0));
    }
}
