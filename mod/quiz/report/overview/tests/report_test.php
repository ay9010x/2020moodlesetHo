<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/overview/report.php');



class quiz_overview_report_testcase extends advanced_testcase {

    public function test_report_sql() {
        global $DB, $SITE;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $quizgenerator = $generator->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(array('course' => $SITE->id,
                'grademethod' => QUIZ_GRADEHIGHEST, 'grade' => 100.0, 'sumgrades' => 10.0,
                'attempts' => 10));

        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $student3 = $generator->create_user();

        $quizid = 123;
        $timestamp = 1234567890;

                $fields = array('quiz', 'userid', 'attempt', 'sumgrades', 'state');
        $attempts = array(
            array($quiz->id, $student1->id, 1, 0.0,  quiz_attempt::FINISHED),
            array($quiz->id, $student1->id, 2, 5.0,  quiz_attempt::FINISHED),
            array($quiz->id, $student1->id, 3, 8.0,  quiz_attempt::FINISHED),
            array($quiz->id, $student1->id, 4, null, quiz_attempt::ABANDONED),
            array($quiz->id, $student1->id, 5, null, quiz_attempt::IN_PROGRESS),
            array($quiz->id, $student2->id, 1, null, quiz_attempt::ABANDONED),
            array($quiz->id, $student2->id, 2, null, quiz_attempt::ABANDONED),
            array($quiz->id, $student2->id, 3, 7.0,  quiz_attempt::FINISHED),
            array($quiz->id, $student2->id, 4, null, quiz_attempt::ABANDONED),
            array($quiz->id, $student2->id, 5, null, quiz_attempt::ABANDONED),
        );

                $uniqueid = 1;
        foreach ($attempts as $attempt) {
            $data = array_combine($fields, $attempt);
            $data['timestart'] = $timestamp + 3600 * $data['attempt'];
            $data['timemodifed'] = $data['timestart'];
            if ($data['state'] == quiz_attempt::FINISHED) {
                $data['timefinish'] = $data['timestart'] + 600;
                $data['timemodifed'] = $data['timefinish'];
            }
            $data['layout'] = '';             $data['uniqueid'] = $uniqueid++;
            $data['preview'] = 0;
            $DB->insert_record('quiz_attempts', $data);
        }

                        $context = context_module::instance($quiz->cmid);
        $cm = get_coursemodule_from_id('quiz', $quiz->cmid);
        $qmsubselect = quiz_report_qm_filter_select($quiz);
        $reportstudents = array($student1->id, $student2->id, $student3->id);

                $reportoptions = new quiz_overview_options('overview', $quiz, $cm, null);
        $reportoptions->attempts = quiz_attempts_report::ENROLLED_ALL;
        $reportoptions->onlygraded = true;
        $reportoptions->states = array(quiz_attempt::IN_PROGRESS, quiz_attempt::OVERDUE, quiz_attempt::FINISHED);

                $table = new quiz_overview_table($quiz, $context, $qmsubselect, $reportoptions,
                array(), $reportstudents, array(1), null);
        $table->define_columns(array('attempt'));
        $table->sortable(true, 'uniqueid');
        $table->define_baseurl(new moodle_url('/mod/quiz/report.php'));
        $table->setup();

                list($fields, $from, $where, $params) = $table->base_sql($reportstudents);
        $table->set_sql($fields, $from, $where, $params);
        $table->query_db(30, false);

                                $this->assertEquals(4, count($table->rawdata));
        $this->assertArrayHasKey($student1->id . '#3', $table->rawdata);
        $this->assertEquals(1, $table->rawdata[$student1->id . '#3']->gradedattempt);
        $this->assertArrayHasKey($student1->id . '#3', $table->rawdata);
        $this->assertEquals(0, $table->rawdata[$student1->id . '#5']->gradedattempt);
        $this->assertArrayHasKey($student2->id . '#3', $table->rawdata);
        $this->assertEquals(1, $table->rawdata[$student2->id . '#3']->gradedattempt);
        $this->assertArrayHasKey($student3->id . '#0', $table->rawdata);
        $this->assertEquals(0, $table->rawdata[$student3->id . '#0']->gradedattempt);
    }
}
