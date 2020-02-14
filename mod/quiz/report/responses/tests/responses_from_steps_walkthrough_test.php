<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/tests/attempt_walkthrough_from_csv_test.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/statistics/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');


class quiz_report_responses_from_steps_testcase extends mod_quiz_attempt_walkthrough_from_csv_testcase {
    protected function get_full_path_of_csv_file($setname, $test) {
                return  __DIR__."/fixtures/{$setname}{$test}.csv";
    }

    protected $files = array('questions', 'steps', 'responses');

    
    public function test_walkthrough_from_csv($quizsettings, $csvdata) {

        $this->resetAfterTest(true);
        question_bank::get_qtype('random')->clear_caches_before_testing();

        $this->create_quiz($quizsettings, $csvdata['questions']);

        $quizattemptids = $this->walkthrough_attempts($csvdata['steps']);

        for ($rowno = 0; $rowno < $csvdata['responses']->getRowCount(); $rowno++) {
            $responsesfromcsv = $csvdata['responses']->getRow($rowno);
            $responses = $this->explode_dot_separated_keys_to_make_subindexs($responsesfromcsv);

            if (!isset($quizattemptids[$responses['quizattempt']])) {
                throw new coding_exception("There is no quizattempt {$responses['quizattempt']}!");
            }
            $this->assert_response_test($quizattemptids[$responses['quizattempt']], $responses);
        }
    }

    protected function assert_response_test($quizattemptid, $responses) {
        $quizattempt = quiz_attempt::create($quizattemptid);

        foreach ($responses['slot'] as $slot => $tests) {
            $slothastests = false;
            foreach ($tests as $test) {
                if ('' !== $test) {
                    $slothastests = true;
                }
            }
            if (!$slothastests) {
                continue;
            }
            $qa = $quizattempt->get_question_attempt($slot);
            $stepswithsubmit = $qa->get_steps_with_submitted_response_iterator();
            $step = $stepswithsubmit[$responses['submittedstepno']];
            if (null === $step) {
                throw new coding_exception("There is no step no {$responses['submittedstepno']} ".
                                           "for slot $slot in quizattempt {$responses['quizattempt']}!");
            }
            foreach (array('responsesummary', 'fraction', 'state') as $column) {
                if (isset($tests[$column]) && $tests[$column] != '') {
                    switch($column) {
                        case 'responsesummary' :
                            $actual = $qa->get_question()->summarise_response($step->get_qt_data());
                            break;
                        case 'fraction' :
                            if (count($stepswithsubmit) == $responses['submittedstepno']) {
                                                                                                $actual = $qa->get_fraction();
                            } else {
                                $actual = $step->get_fraction();
                            }
                           break;
                        case 'state' :
                            if (count($stepswithsubmit) == $responses['submittedstepno']) {
                                                                                                $state = $qa->get_state();
                            } else {
                                $state = $step->get_state();
                            }
                            $actual = substr(get_class($state), strlen('question_state_'));
                    }
                    $expected = $tests[$column];
                    $failuremessage = "Error in  quizattempt {$responses['quizattempt']} in $column, slot $slot, ".
                    "submittedstepno {$responses['submittedstepno']}";
                    $this->assertEquals($expected, $actual, $failuremessage);
                }
            }
        }
    }
}
