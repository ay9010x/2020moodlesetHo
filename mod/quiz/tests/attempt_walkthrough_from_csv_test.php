<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');


class mod_quiz_attempt_walkthrough_from_csv_testcase extends advanced_testcase {

    protected $files = array('questions', 'steps', 'results');

    
    protected $quiz;

    
    protected $randqids;

    
    public function test_walkthrough_from_csv($quizsettings, $csvdata) {

                
        $this->create_quiz_simulate_attempts_and_check_results($quizsettings, $csvdata);
    }

    public function create_quiz($quizsettings, $qs) {
        global $SITE, $DB;
        $this->setAdminUser();

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $slots = array();
        $qidsbycat = array();
        $sumofgrades = 0;
        for ($rowno = 0; $rowno < $qs->getRowCount(); $rowno++) {
            $q = $this->explode_dot_separated_keys_to_make_subindexs($qs->getRow($rowno));

            $catname = array('name' => $q['cat']);
            if (!$cat = $DB->get_record('question_categories', array('name' => $q['cat']))) {
                $cat = $questiongenerator->create_question_category($catname);
            }
            $q['catid'] = $cat->id;
            foreach (array('which' => null, 'overrides' => array()) as $key => $default) {
                if (empty($q[$key])) {
                    $q[$key] = $default;
                }
            }

            if ($q['type'] !== 'random') {
                                $overrides = array('category' => $cat->id, 'defaultmark' => $q['mark']) + $q['overrides'];
                $question = $questiongenerator->create_question($q['type'], $q['which'], $overrides);
                $q['id'] = $question->id;

                if (!isset($qidsbycat[$q['cat']])) {
                    $qidsbycat[$q['cat']] = array();
                }
                if (!empty($q['which'])) {
                    $name = $q['type'].'_'.$q['which'];
                } else {
                    $name = $q['type'];
                }
                $qidsbycat[$q['catid']][$name] = $q['id'];
            }
            if (!empty($q['slot'])) {
                $slots[$q['slot']] = $q;
                $sumofgrades += $q['mark'];
            }
        }

        ksort($slots);

                $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

                $aggregratedsettings = $quizsettings + array('course' => $SITE->id,
                                                     'questionsperpage' => 0,
                                                     'grade' => 100.0,
                                                     'sumgrades' => $sumofgrades);

        $this->quiz = $quizgenerator->create_instance($aggregratedsettings);

        $this->randqids = array();
        foreach ($slots as $slotno => $slotquestion) {
            if ($slotquestion['type'] !== 'random') {
                quiz_add_quiz_question($slotquestion['id'], $this->quiz, 0, $slotquestion['mark']);
            } else {
                quiz_add_random_questions($this->quiz, 0, $slotquestion['catid'], 1, 0);
                $this->randqids[$slotno] = $qidsbycat[$slotquestion['catid']];
            }
        }
    }

    
    protected function create_quiz_simulate_attempts_and_check_results($quizsettings, $csvdata) {
        $this->resetAfterTest(true);
        question_bank::get_qtype('random')->clear_caches_before_testing();

        $this->create_quiz($quizsettings, $csvdata['questions']);

        $attemptids = $this->walkthrough_attempts($csvdata['steps']);

        if (isset($csvdata['results'])) {
            $this->check_attempts_results($csvdata['results'], $attemptids);
        }
    }

    
    protected function get_full_path_of_csv_file($setname, $test) {
        return  __DIR__."/fixtures/{$setname}{$test}.csv";
    }

    
    protected function load_csv_data_file($setname, $test='') {
        $files = array($setname => $this->get_full_path_of_csv_file($setname, $test));
        return $this->createCsvDataSet($files)->getTable($setname);
    }

    
    protected function explode_dot_separated_keys_to_make_subindexs(array $row) {
        $parts = array();
        foreach ($row as $columnkey => $value) {
            $newkeys = explode('.', trim($columnkey));
            $placetoputvalue =& $parts;
            foreach ($newkeys as $newkeydepth => $newkey) {
                if ($newkeydepth + 1 === count($newkeys)) {
                    $placetoputvalue[$newkey] = $value;
                } else {
                                        if (!isset($placetoputvalue[$newkey])) {
                        $placetoputvalue[$newkey] = array();
                    }
                    $placetoputvalue =& $placetoputvalue[$newkey];
                }
            }
        }
        return $parts;
    }

    
    public function get_data_for_walkthrough() {
        $quizzes = $this->load_csv_data_file('quizzes');
        $datasets = array();
        for ($rowno = 0; $rowno < $quizzes->getRowCount(); $rowno++) {
            $quizsettings = $quizzes->getRow($rowno);
            $dataset = array();
            foreach ($this->files as $file) {
                if (file_exists($this->get_full_path_of_csv_file($file, $quizsettings['testnumber']))) {
                    $dataset[$file] = $this->load_csv_data_file($file, $quizsettings['testnumber']);
                }
            }
            $datasets[] = array($quizsettings, $dataset);
        }
        return $datasets;
    }

    
    protected function walkthrough_attempts($steps) {
        global $DB;
        $attemptids = array();
        for ($rowno = 0; $rowno < $steps->getRowCount(); $rowno++) {

            $step = $this->explode_dot_separated_keys_to_make_subindexs($steps->getRow($rowno));
                        $username = array('firstname' => $step['firstname'],
                              'lastname'  => $step['lastname']);

            if (!$user = $DB->get_record('user', $username)) {
                $user = $this->getDataGenerator()->create_user($username);
            }

            if (!isset($attemptids[$step['quizattempt']])) {
                                $quizobj = quiz::create($this->quiz->id, $user->id);
                $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
                $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

                $prevattempts = quiz_get_user_attempts($this->quiz->id, $user->id, 'all', true);
                $attemptnumber = count($prevattempts) + 1;
                $timenow = time();
                $attempt = quiz_create_attempt($quizobj, $attemptnumber, false, $timenow, false, $user->id);
                                if (!isset($step['variants'])) {
                    $step['variants'] = array();
                }
                if (isset($step['randqs'])) {
                                        foreach ($step['randqs'] as $slotno => $randqname) {
                        $step['randqs'][$slotno] = $this->randqids[$slotno][$randqname];
                    }
                } else {
                    $step['randqs'] = array();
                }

                quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, $timenow, $step['randqs'], $step['variants']);
                quiz_attempt_save_started($quizobj, $quba, $attempt);
                $attemptid = $attemptids[$step['quizattempt']] = $attempt->id;
            } else {
                $attemptid = $attemptids[$step['quizattempt']];
            }

                        $attemptobj = quiz_attempt::create($attemptid);
            $attemptobj->process_submitted_actions($timenow, false, $step['responses']);

                        if (!isset($step['finished']) || ($step['finished'] == 1)) {
                $attemptobj = quiz_attempt::create($attemptid);
                $attemptobj->process_finish($timenow, false);
            }
        }
        return $attemptids;
    }

    
    protected function check_attempts_results($results, $attemptids) {
        for ($rowno = 0; $rowno < $results->getRowCount(); $rowno++) {
            $result = $this->explode_dot_separated_keys_to_make_subindexs($results->getRow($rowno));
                        $attemptobj = quiz_attempt::create($attemptids[$result['quizattempt']]);
            $this->check_attempt_results($result, $attemptobj);
        }
    }

    
    protected function check_attempt_results($result, $attemptobj) {
        foreach ($result as $fieldname => $value) {
            if ($value === '!NULL!') {
                $value = null;
            }
            switch ($fieldname) {
                case 'quizattempt' :
                    break;
                case 'attemptnumber' :
                    $this->assertEquals($value, $attemptobj->get_attempt_number());
                    break;
                case 'slots' :
                    foreach ($value as $slotno => $slottests) {
                        foreach ($slottests as $slotfieldname => $slotvalue) {
                            switch ($slotfieldname) {
                                case 'mark' :
                                    $this->assertEquals(round($slotvalue, 2), $attemptobj->get_question_mark($slotno),
                                                        "Mark for slot $slotno of attempt {$result['quizattempt']}.");
                                    break;
                                default :
                                    throw new coding_exception('Unknown slots sub field column in csv file '
                                                               .s($slotfieldname));
                            }
                        }
                    }
                    break;
                case 'finished' :
                    $this->assertEquals((bool)$value, $attemptobj->is_finished());
                    break;
                case 'summarks' :
                    $this->assertEquals($value, $attemptobj->get_sum_marks(), "Sum of marks of attempt {$result['quizattempt']}.");
                    break;
                case 'quizgrade' :
                                        $grades = quiz_get_user_grades($attemptobj->get_quiz(), $attemptobj->get_userid());
                    $grade = array_shift($grades);
                    $this->assertEquals($value, $grade->rawgrade, "Quiz grade for attempt {$result['quizattempt']}.");
                    break;
                case 'gradebookgrade' :
                                        $gradebookgrades = grade_get_grades($attemptobj->get_courseid(),
                                                        'mod', 'quiz',
                                                        $attemptobj->get_quizid(),
                                                        $attemptobj->get_userid());
                    $gradebookitem = array_shift($gradebookgrades->items);
                    $gradebookgrade = array_shift($gradebookitem->grades);
                    $this->assertEquals($value, $gradebookgrade->grade, "Gradebook grade for attempt {$result['quizattempt']}.");
                    break;
                default :
                    throw new coding_exception('Unknown column in csv file '.s($fieldname));
            }
        }
    }
}
