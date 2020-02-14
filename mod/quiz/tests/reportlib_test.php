<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');



class mod_quiz_reportlib_testcase extends advanced_testcase {
    public function test_quiz_report_index_by_keys() {
        $datum = array();
        $object = new stdClass();
        $object->qid = 3;
        $object->aid = 101;
        $object->response = '';
        $object->grade = 3;
        $datum[] = $object;

        $indexed = quiz_report_index_by_keys($datum, array('aid', 'qid'));

        $this->assertEquals($indexed[101][3]->qid, 3);
        $this->assertEquals($indexed[101][3]->aid, 101);
        $this->assertEquals($indexed[101][3]->response, '');
        $this->assertEquals($indexed[101][3]->grade, 3);

        $indexed = quiz_report_index_by_keys($datum, array('aid', 'qid'), false);

        $this->assertEquals($indexed[101][3][0]->qid, 3);
        $this->assertEquals($indexed[101][3][0]->aid, 101);
        $this->assertEquals($indexed[101][3][0]->response, '');
        $this->assertEquals($indexed[101][3][0]->grade, 3);
    }

    public function test_quiz_report_scale_summarks_as_percentage() {
        $quiz = new stdClass();
        $quiz->sumgrades = 10;
        $quiz->decimalpoints = 2;

        $this->assertEquals('12.34567%',
            quiz_report_scale_summarks_as_percentage(1.234567, $quiz, false));
        $this->assertEquals('12.35%',
            quiz_report_scale_summarks_as_percentage(1.234567, $quiz, true));
        $this->assertEquals('-',
            quiz_report_scale_summarks_as_percentage('-', $quiz, true));
    }

    public function test_quiz_report_qm_filter_select_only_one_attempt_allowed() {
        $quiz = new stdClass();
        $quiz->attempts = 1;
        $this->assertSame('', quiz_report_qm_filter_select($quiz));
    }

    public function test_quiz_report_qm_filter_select_average() {
        $quiz = new stdClass();
        $quiz->attempts = 10;
        $quiz->grademethod = QUIZ_GRADEAVERAGE;
        $this->assertSame('', quiz_report_qm_filter_select($quiz));
    }

    public function test_quiz_report_qm_filter_select_first_last_best() {
        global $DB;
        $this->resetAfterTest();

        $fakeattempt = new stdClass();
        $fakeattempt->userid = 123;
        $fakeattempt->quiz = 456;
        $fakeattempt->layout = '1,2,0,3,4,0,5';
        $fakeattempt->state = quiz_attempt::FINISHED;

                                                                                        
        $fakeattempt->attempt = 3;
        $fakeattempt->sumgrades = 50;
        $fakeattempt->uniqueid = 13;
        $DB->insert_record('quiz_attempts', $fakeattempt);

        $fakeattempt->attempt = 2;
        $fakeattempt->sumgrades = 50;
        $fakeattempt->uniqueid = 26;
        $DB->insert_record('quiz_attempts', $fakeattempt);

        $fakeattempt->attempt = 4;
        $fakeattempt->sumgrades = null;
        $fakeattempt->uniqueid = 39;
        $fakeattempt->state = quiz_attempt::IN_PROGRESS;
        $DB->insert_record('quiz_attempts', $fakeattempt);

        $fakeattempt->attempt = 1;
        $fakeattempt->sumgrades = 30;
        $fakeattempt->uniqueid = 52;
        $fakeattempt->state = quiz_attempt::FINISHED;
        $DB->insert_record('quiz_attempts', $fakeattempt);

        $fakeattempt->attempt = 1;
        $fakeattempt->userid = 1;
        $fakeattempt->sumgrades = 100;
        $fakeattempt->uniqueid = 65;
        $DB->insert_record('quiz_attempts', $fakeattempt);

        $quiz = new stdClass();
        $quiz->attempts = 10;

        $quiz->grademethod = QUIZ_ATTEMPTFIRST;
        $firstattempt = $DB->get_records_sql("
                SELECT * FROM {quiz_attempts} quiza WHERE userid = ? AND quiz = ? AND "
                        . quiz_report_qm_filter_select($quiz), array(123, 456));
        $this->assertEquals(1, count($firstattempt));
        $firstattempt = reset($firstattempt);
        $this->assertEquals(1, $firstattempt->attempt);

        $quiz->grademethod = QUIZ_ATTEMPTLAST;
        $lastattempt = $DB->get_records_sql("
                SELECT * FROM {quiz_attempts} quiza WHERE userid = ? AND quiz = ? AND "
                . quiz_report_qm_filter_select($quiz), array(123, 456));
        $this->assertEquals(1, count($lastattempt));
        $lastattempt = reset($lastattempt);
        $this->assertEquals(3, $lastattempt->attempt);

        $quiz->attempts = 0;
        $quiz->grademethod = QUIZ_GRADEHIGHEST;
        $bestattempt = $DB->get_records_sql("
                SELECT * FROM {quiz_attempts} qa_alias WHERE userid = ? AND quiz = ? AND "
                . quiz_report_qm_filter_select($quiz, 'qa_alias'), array(123, 456));
        $this->assertEquals(1, count($bestattempt));
        $bestattempt = reset($bestattempt);
        $this->assertEquals(2, $bestattempt->attempt);
    }
}
