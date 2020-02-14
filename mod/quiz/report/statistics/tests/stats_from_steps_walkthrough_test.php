<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/tests/attempt_walkthrough_from_csv_test.php');
require_once($CFG->dirroot . '/mod/quiz/report/default.php');
require_once($CFG->dirroot . '/mod/quiz/report/statistics/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');


class quiz_report_statistics_from_steps_testcase extends mod_quiz_attempt_walkthrough_from_csv_testcase {

    
    protected $report;

    protected function get_full_path_of_csv_file($setname, $test) {
                return  __DIR__."/fixtures/{$setname}{$test}.csv";
    }

    protected $files = array('questions', 'steps', 'results', 'qstats', 'responsecounts');

    
    public function test_walkthrough_from_csv($quizsettings, $csvdata) {

        $this->create_quiz_simulate_attempts_and_check_results($quizsettings, $csvdata);

        $whichattempts = QUIZ_GRADEAVERAGE;         $whichtries = question_attempt::ALL_TRIES;
        $groupstudents = array();
        list($questions, $quizstats, $questionstats, $qubaids) =
                    $this->check_stats_calculations_and_response_analysis($csvdata, $whichattempts, $whichtries, $groupstudents);
        if ($quizsettings['testnumber'] === '00') {
            $this->check_variants_count_for_quiz_00($questions, $questionstats, $whichtries, $qubaids);
            $this->check_quiz_stats_for_quiz_00($quizstats);
        }
    }

    
    protected function check_question_stats($qstats, $questionstats) {
        for ($rowno = 0; $rowno < $qstats->getRowCount(); $rowno++) {
            $slotqstats = $qstats->getRow($rowno);
            foreach ($slotqstats as $statname => $slotqstat) {
                if (!in_array($statname, array('slot', 'subqname'))  && $slotqstat !== '') {
                    $this->assert_stat_equals($slotqstat,
                                              $questionstats,
                                              $slotqstats['slot'],
                                              $slotqstats['subqname'],
                                              $slotqstats['variant'],
                                              $statname);
                }
            }
                        $this->assert_stat_equals(!empty($slotqstats['subqname']),
                                      $questionstats,
                                      $slotqstats['slot'],
                                      $slotqstats['subqname'],
                                      $slotqstats['variant'],
                                      'subquestion');
        }
    }

    
    protected function assert_stat_equals($expected, $questionstats, $slot, $subqname, $variant, $statname) {

        if ($variant === '' && $subqname === '') {
            $actual = $questionstats->for_slot($slot)->{$statname};
        } else if ($subqname !== '') {
            $actual = $questionstats->for_subq($this->randqids[$slot][$subqname])->{$statname};
        } else {
            $actual = $questionstats->for_slot($slot, $variant)->{$statname};
        }
        $message = "$statname for slot $slot";
        if ($expected === '**NULL**') {
            $this->assertEquals(null, $actual, $message);
        } else if (is_bool($expected)) {
            $this->assertEquals($expected, $actual, $message);
        } else if (is_numeric($expected)) {
            switch ($statname) {
                case 'covariance' :
                case 'discriminationindex' :
                case 'discriminativeefficiency' :
                case 'effectiveweight' :
                    $precision = 1e-5;
                    break;
                default :
                    $precision = 1e-6;
            }
            $delta = abs($expected) * $precision;
            $this->assertEquals((float)$expected, $actual, $message, $delta);
        } else {
            $this->assertEquals($expected, $actual, $message);
        }
    }

    protected function assert_response_count_equals($question, $qubaids, $expected, $whichtries) {
        $responesstats = new \core_question\statistics\responses\analyser($question);
        $analysis = $responesstats->load_cached($qubaids, $whichtries);
        if (!isset($expected['subpart'])) {
            $subpart = 1;
        } else {
            $subpart = $expected['subpart'];
        }
        list($subpartid, $responseclassid) = $this->get_response_subpart_and_class_id($question,
                                                                                      $subpart,
                                                                                      $expected['modelresponse']);

        $subpartanalysis = $analysis->get_analysis_for_subpart($expected['variant'], $subpartid);
        $responseclassanalysis = $subpartanalysis->get_response_class($responseclassid);
        $actualresponsecounts = $responseclassanalysis->data_for_question_response_table('', '');

        foreach ($actualresponsecounts as $actualresponsecount) {
            if ($actualresponsecount->response == $expected['actualresponse'] || count($actualresponsecounts) == 1) {
                $i = 1;
                $partofanalysis = " slot {$expected['slot']}, rand q '{$expected['randq']}', variant {$expected['variant']}, ".
                                    "for expected model response {$expected['modelresponse']}, ".
                                    "actual response {$expected['actualresponse']}";
                while (isset($expected['count'.$i])) {
                    if ($expected['count'.$i] != 0) {
                        $this->assertTrue(isset($actualresponsecount->trycount[$i]),
                            "There is no count at all for try $i on ".$partofanalysis);
                        $this->assertEquals($expected['count'.$i], $actualresponsecount->trycount[$i],
                                            "Count for try $i on ".$partofanalysis);
                    }
                    $i++;
                }
                if (isset($expected['totalcount'])) {
                    $this->assertEquals($expected['totalcount'], $actualresponsecount->totalcount,
                                        "Total count on ".$partofanalysis);
                }
                return;
            }
        }
        throw new coding_exception("Expected response '{$expected['actualresponse']}' not found.");
    }

    protected function get_response_subpart_and_class_id($question, $subpart, $modelresponse) {
        $qtypeobj = question_bank::get_qtype($question->qtype, false);
        $possibleresponses = $qtypeobj->get_possible_responses($question);
        $possibleresponsesubpartids = array_keys($possibleresponses);
        if (!isset($possibleresponsesubpartids[$subpart - 1])) {
            throw new coding_exception("Subpart '{$subpart}' not found.");
        }
        $subpartid = $possibleresponsesubpartids[$subpart - 1];

        if ($modelresponse == '[NO RESPONSE]') {
            return array($subpartid, null);

        } else if ($modelresponse == '[NO MATCH]') {
            return array($subpartid, 0);
        }

        $modelresponses = array();
        foreach ($possibleresponses[$subpartid] as $responseclassid => $subpartpossibleresponse) {
            $modelresponses[$responseclassid] = $subpartpossibleresponse->responseclass;
        }
        $this->assertContains($modelresponse, $modelresponses);
        $responseclassid = array_search($modelresponse, $modelresponses);
        return array($subpartid, $responseclassid);
    }

    
    protected function check_response_counts($responsecounts, $qubaids, $questions, $whichtries) {
        for ($rowno = 0; $rowno < $responsecounts->getRowCount(); $rowno++) {
            $expected = $responsecounts->getRow($rowno);
            $defaultsforexpected = array('randq' => '', 'variant' => '1', 'subpart' => '1');
            foreach ($defaultsforexpected as $key => $expecteddefault) {
                if (!isset($expected[$key])) {
                    $expected[$key] = $expecteddefault;
                }
            }
            if ($expected['randq'] == '') {
                $question = $questions[$expected['slot']];
            } else {
                $qid = $this->randqids[$expected['slot']][$expected['randq']];
                $question = question_finder::get_instance()->load_question_data($qid);
            }
            $this->assert_response_count_equals($question, $qubaids, $expected, $whichtries);
        }
    }

    
    protected function check_variants_count_for_quiz_00($questions, $questionstats, $whichtries, $qubaids) {
        $expectedvariantcounts = array(2 => array(1  => 6,
                                                  4  => 4,
                                                  5  => 3,
                                                  6  => 4,
                                                  7  => 2,
                                                  8  => 5,
                                                  10 => 1));

        foreach ($questions as $slot => $question) {
            if (!question_bank::get_qtype($question->qtype, false)->can_analyse_responses()) {
                continue;
            }
            $responesstats = new \core_question\statistics\responses\analyser($question);
            $this->assertTimeCurrent($responesstats->get_last_analysed_time($qubaids, $whichtries));
            $analysis = $responesstats->load_cached($qubaids, $whichtries);
            $variantsnos = $analysis->get_variant_nos();
            if (isset($expectedvariantcounts[$slot])) {
                                $this->assertEquals(array_keys($expectedvariantcounts[$slot]), $variantsnos, '', 0, 10, true);
            } else {
                $this->assertEquals(array(1), $variantsnos);
            }
            $totalspervariantno = array();
            foreach ($variantsnos as $variantno) {

                $subpartids = $analysis->get_subpart_ids($variantno);
                foreach ($subpartids as $subpartid) {
                    if (!isset($totalspervariantno[$subpartid])) {
                        $totalspervariantno[$subpartid] = array();
                    }
                    $totalspervariantno[$subpartid][$variantno] = 0;

                    $subpartanalysis = $analysis->get_analysis_for_subpart($variantno, $subpartid);
                    $classids = $subpartanalysis->get_response_class_ids();
                    foreach ($classids as $classid) {
                        $classanalysis = $subpartanalysis->get_response_class($classid);
                        $actualresponsecounts = $classanalysis->data_for_question_response_table('', '');
                        foreach ($actualresponsecounts as $actualresponsecount) {
                            $totalspervariantno[$subpartid][$variantno] += $actualresponsecount->totalcount;
                        }
                    }
                }
            }
                                    if ($slot != 5) {
                                                                                                foreach ($totalspervariantno as $totalpervariantno) {
                    if (isset($expectedvariantcounts[$slot])) {
                                                                        $this->assertEquals($expectedvariantcounts[$slot],
                                            $totalpervariantno,
                                            "Totals responses do not add up in response analysis for slot {$slot}.",
                                            0,
                                            10,
                                            true);
                    } else {
                        $this->assertEquals(25,
                                            array_sum($totalpervariantno),
                                            "Totals responses do not add up in response analysis for slot {$slot}.");
                    }
                }
            }
        }

        foreach ($expectedvariantcounts as $slot => $expectedvariantcount) {
            foreach ($expectedvariantcount as $variantno => $s) {
                $this->assertEquals($s, $questionstats->for_slot($slot, $variantno)->s);
            }
        }
    }

    
    protected function check_quiz_stats_for_quiz_00($quizstats) {
        $quizstatsexpected = array(
            'median'             => 4.5,
            'firstattemptsavg'   => 4.617333332,
            'allattemptsavg'     => 4.617333332,
            'firstattemptscount' => 25,
            'allattemptscount'   => 25,
            'standarddeviation'  => 0.8117265554,
            'skewness'           => -0.092502502,
            'kurtosis'           => -0.7073968557,
            'cic'                => -87.2230935542,
            'errorratio'         => 136.8294900795,
            'standarderror'      => 1.1106813066
        );

        foreach ($quizstatsexpected as $statname => $statvalue) {
            $this->assertEquals($statvalue, $quizstats->$statname, $quizstats->$statname, abs($statvalue) * 1.5e-5);
        }
    }

    
    protected function check_stats_calculations_and_response_analysis($csvdata, $whichattempts, $whichtries, $groupstudents) {
        $this->report = new quiz_statistics_report();
        $questions = $this->report->load_and_initialise_questions_for_calculations($this->quiz);
        list($quizstats, $questionstats) = $this->report->get_all_stats_and_analysis($this->quiz,
                                                                                     $whichattempts,
                                                                                     $whichtries,
                                                                                     $groupstudents,
                                                                                     $questions);

        $qubaids = quiz_statistics_qubaids_condition($this->quiz->id, $groupstudents, $whichattempts);

                        $quizcalc = new \quiz_statistics\calculator();
                $this->assertTimeCurrent($quizcalc->get_last_calculated_time($qubaids));

        $qcalc = new \core_question\statistics\questions\calculator($questions);
        $this->assertTimeCurrent($qcalc->get_last_calculated_time($qubaids));

        if (isset($csvdata['responsecounts'])) {
            $this->check_response_counts($csvdata['responsecounts'], $qubaids, $questions, $whichtries);
        }
        if (isset($csvdata['qstats'])) {
            $this->check_question_stats($csvdata['qstats'], $questionstats);
            return array($questions, $quizstats, $questionstats, $qubaids);
        }
        return array($questions, $quizstats, $questionstats, $qubaids);
    }

}
