<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class testable_mod_quiz_external extends mod_quiz_external {

    
    public static function validate_attempt($params, $checkaccessrules = true, $failifoverdue = true) {
        return parent::validate_attempt($params, $checkaccessrules, $failifoverdue);
    }

    
    public static function validate_attempt_review($params) {
        return parent::validate_attempt_review($params);
    }
}


class mod_quiz_external_testcase extends externallib_advanced_testcase {

    
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->quiz = $this->getDataGenerator()->create_module('quiz', array('course' => $this->course->id));
        $this->context = context_module::instance($this->quiz->cmid);
        $this->cm = get_coursemodule_from_instance('quiz', $this->quiz->id);

                $this->student = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

                $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');
    }

    
    private function create_quiz_with_questions($startattempt = false, $finishattempt = false) {

                $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $data = array('course' => $this->course->id,
                      'sumgrades' => 2);
        $quiz = $quizgenerator->create_instance($data);
        $context = context_module::instance($quiz->cmid);

                $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);
        $question = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

        $quizobj = quiz::create($quiz->id, $this->student->id);

                $item = grade_item::fetch(array('courseid' => $this->course->id, 'itemtype' => 'mod',
                                        'itemmodule' => 'quiz', 'iteminstance' => $quiz->id, 'outcomeid' => null));
        $item->gradepass = 80;
        $item->update();

        if ($startattempt or $finishattempt) {
                        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
            $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

            $timenow = time();
            $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $this->student->id);
            quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
            quiz_attempt_save_started($quizobj, $quba, $attempt);
            $attemptobj = quiz_attempt::create($attempt->id);

            if ($finishattempt) {
                                $tosubmit = array(1 => array('answer' => '3.14'));
                $attemptobj->process_submitted_actions(time(), false, $tosubmit);

                                $attemptobj->process_finish(time(), false);
            }
            return array($quiz, $context, $quizobj, $attempt, $attemptobj, $quba);
        } else {
            return array($quiz, $context, $quizobj);
        }

    }

    
    public function test_mod_quiz_get_quizzes_by_courses() {
        global $DB;

                $course2 = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course2->id;
        $quiz2 = self::getDataGenerator()->create_module('quiz', $record);

                $enrol = enrol_get_plugin('manual');
        $enrolinstances = enrol_get_instances($course2->id, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance2 = $courseenrolinstance;
                break;
            }
        }
        $enrol->enrol_user($instance2, $this->student->id, $this->studentrole->id);

        self::setUser($this->student);

        $returndescription = mod_quiz_external::get_quizzes_by_courses_returns();

                        $allusersfields = array('id', 'coursemodule', 'course', 'name', 'intro', 'introformat', 'timeopen', 'timeclose',
                                'grademethod', 'section', 'visible', 'groupmode', 'groupingid');
        $userswithaccessfields = array('timelimit', 'attempts', 'attemptonlast', 'grademethod', 'decimalpoints',
                                        'questiondecimalpoints', 'reviewattempt', 'reviewcorrectness', 'reviewmarks',
                                        'reviewspecificfeedback', 'reviewgeneralfeedback', 'reviewrightanswer',
                                        'reviewoverallfeedback', 'questionsperpage', 'navmethod', 'sumgrades', 'grade',
                                        'browsersecurity', 'delay1', 'delay2', 'showuserpicture', 'showblocks',
                                        'completionattemptsexhausted', 'completionpass', 'autosaveperiod', 'hasquestions',
                                        'hasfeedback', 'overduehandling', 'graceperiod', 'preferredbehaviour', 'canredoquestions');
        $managerfields = array('shuffleanswers', 'timecreated', 'timemodified', 'password', 'subnet');

                $quiz1 = $this->quiz;
        $quiz1->coursemodule = $quiz1->cmid;
        $quiz1->introformat = 1;
        $quiz1->section = 0;
        $quiz1->visible = true;
        $quiz1->groupmode = 0;
        $quiz1->groupingid = 0;
        $quiz1->hasquestions = 0;
        $quiz1->hasfeedback = 0;
        $quiz1->autosaveperiod = get_config('quiz', 'autosaveperiod');

        $quiz2->coursemodule = $quiz2->cmid;
        $quiz2->introformat = 1;
        $quiz2->section = 0;
        $quiz2->visible = true;
        $quiz2->groupmode = 0;
        $quiz2->groupingid = 0;
        $quiz2->hasquestions = 0;
        $quiz2->hasfeedback = 0;
        $quiz2->autosaveperiod = get_config('quiz', 'autosaveperiod');

        foreach (array_merge($allusersfields, $userswithaccessfields) as $field) {
            $expected1[$field] = $quiz1->{$field};
            $expected2[$field] = $quiz2->{$field};
        }

        $expectedquizzes = array($expected2, $expected1);

                $result = mod_quiz_external::get_quizzes_by_courses(array($course2->id, $this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedquizzes, $result['quizzes']);
        $this->assertCount(0, $result['warnings']);

                $result = mod_quiz_external::get_quizzes_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedquizzes, $result['quizzes']);
        $this->assertCount(0, $result['warnings']);

                $enrol->unenrol_user($instance2, $this->student->id);
        array_shift($expectedquizzes);

                $result = mod_quiz_external::get_quizzes_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedquizzes, $result['quizzes']);

                $result = mod_quiz_external::get_quizzes_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);

                self::setUser($this->teacher);

        foreach ($managerfields as $field) {
            $expectedquizzes[0][$field] = $quiz1->{$field};
        }

        $result = mod_quiz_external::get_quizzes_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedquizzes, $result['quizzes']);

                self::setAdminUser();

        $result = mod_quiz_external::get_quizzes_by_courses(array($this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedquizzes, $result['quizzes']);

                $enrol->enrol_user($instance2, $this->student->id);

        self::setUser($this->student);

        $quiz2->timeclose = time() - DAYSECS;
        $DB->update_record('quiz', $quiz2);

        $result = mod_quiz_external::get_quizzes_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertCount(2, $result['quizzes']);
                $this->assertCount(4, $result['quizzes'][0]);
        $this->assertEquals($quiz2->id, $result['quizzes'][0]['id']);
        $this->assertEquals($quiz2->coursemodule, $result['quizzes'][0]['coursemodule']);
        $this->assertEquals($quiz2->course, $result['quizzes'][0]['course']);
        $this->assertEquals($quiz2->name, $result['quizzes'][0]['name']);
        $this->assertEquals($quiz2->course, $result['quizzes'][0]['course']);

        $this->assertFalse(isset($result['quizzes'][0]['timelimit']));

    }

    
    public function test_view_quiz() {
        global $DB;

                try {
            mod_quiz_external::view_quiz(0);
            $this->fail('Exception expected due to invalid mod_quiz instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);
        try {
            mod_quiz_external::view_quiz($this->quiz->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_quiz_external::view_quiz($this->quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::view_quiz_returns(), $result);
        $this->assertTrue($result['status']);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_quiz\event\course_module_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $moodlequiz = new \moodle_url('/mod/quiz/view.php', array('id' => $this->cm->id));
        $this->assertEquals($moodlequiz, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/quiz:view', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            mod_quiz_external::view_quiz($this->quiz->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

    }

    
    public function test_get_user_attempts() {

                list($quiz, $context, $quizobj, $attempt, $attemptobj) = $this->create_quiz_with_questions(true, true);

        $this->setUser($this->student);
        $result = mod_quiz_external::get_user_attempts($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(1, $result['attempts']);
        $this->assertEquals($attempt->id, $result['attempts'][0]['id']);
        $this->assertEquals($quiz->id, $result['attempts'][0]['quiz']);
        $this->assertEquals($this->student->id, $result['attempts'][0]['userid']);
        $this->assertEquals(1, $result['attempts'][0]['attempt']);

                $result = mod_quiz_external::get_user_attempts($quiz->id, 0, 'finished', false);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(1, $result['attempts']);
        $this->assertEquals($attempt->id, $result['attempts'][0]['id']);

                $result = mod_quiz_external::get_user_attempts($quiz->id, 0, 'all', false);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(1, $result['attempts']);
        $this->assertEquals($attempt->id, $result['attempts'][0]['id']);

                $result = mod_quiz_external::get_user_attempts($quiz->id, 0, 'unfinished', false);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(0, $result['attempts']);

                $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 2, false, $timenow, false, $this->student->id);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

                $result = mod_quiz_external::get_user_attempts($quiz->id, 0, 'all', false);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(2, $result['attempts']);

                $result = mod_quiz_external::get_user_attempts($quiz->id, 0, 'unfinished', false);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(1, $result['attempts']);

                $this->setUser($this->teacher);
        $result = mod_quiz_external::get_user_attempts($quiz->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(1, $result['attempts']);
        $this->assertEquals($this->student->id, $result['attempts'][0]['userid']);

        $result = mod_quiz_external::get_user_attempts($quiz->id, $this->student->id, 'all');
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_attempts_returns(), $result);

        $this->assertCount(2, $result['attempts']);
        $this->assertEquals($this->student->id, $result['attempts'][0]['userid']);

                try {
            mod_quiz_external::get_user_attempts($quiz->id, $this->student->id, 'INVALID_PARAMETER');
            $this->fail('Exception expected due to missing capability.');
        } catch (invalid_parameter_exception $e) {
            $this->assertEquals('invalidparameter', $e->errorcode);
        }
    }

    
    public function test_get_user_best_grade() {
        global $DB;

        $this->setUser($this->student);

        $result = mod_quiz_external::get_user_best_grade($this->quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_best_grade_returns(), $result);

                $this->assertFalse($result['hasgrade']);
        $this->assertTrue(!isset($result['grade']));

        $grade = new stdClass();
        $grade->quiz = $this->quiz->id;
        $grade->userid = $this->student->id;
        $grade->grade = 8.9;
        $grade->timemodified = time();
        $grade->id = $DB->insert_record('quiz_grades', $grade);

        $result = mod_quiz_external::get_user_best_grade($this->quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_best_grade_returns(), $result);

                $this->assertTrue($result['hasgrade']);
        $this->assertEquals(8.9, $result['grade']);

                $anotherstudent = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($anotherstudent->id, $this->course->id, $this->studentrole->id, 'manual');

        try {
            mod_quiz_external::get_user_best_grade($this->quiz->id, $anotherstudent->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (required_capability_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

                $this->setUser($this->teacher);

        $result = mod_quiz_external::get_user_best_grade($this->quiz->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_user_best_grade_returns(), $result);

        $this->assertTrue($result['hasgrade']);
        $this->assertEquals(8.9, $result['grade']);

                try {
            mod_quiz_external::get_user_best_grade($this->quiz->id, -1);
            $this->fail('Exception expected due to missing capability.');
        } catch (dml_missing_record_exception $e) {
            $this->assertEquals('invaliduser', $e->errorcode);
        }

                $DB->delete_records('quiz_grades', array('id' => $grade->id));

    }
    
    public function test_get_combined_review_options() {
        global $DB;

                $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $data = array('course' => $this->course->id,
                      'sumgrades' => 1);
        $quiz = $quizgenerator->create_instance($data);

                $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

        $quizobj = quiz::create($quiz->id, $this->student->id);

                $item = grade_item::fetch(array('courseid' => $this->course->id, 'itemtype' => 'mod',
                                        'itemmodule' => 'quiz', 'iteminstance' => $quiz->id, 'outcomeid' => null));
        $item->gradepass = 80;
        $item->update();

                $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $this->student->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $this->setUser($this->student);

        $result = mod_quiz_external::get_combined_review_options($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_combined_review_options_returns(), $result);

                $expected = array(
            "someoptions" => array(
                array("name" => "feedback", "value" => 1),
                array("name" => "generalfeedback", "value" => 1),
                array("name" => "rightanswer", "value" => 1),
                array("name" => "overallfeedback", "value" => 0),
                array("name" => "marks", "value" => 2),
            ),
            "alloptions" => array(
                array("name" => "feedback", "value" => 1),
                array("name" => "generalfeedback", "value" => 1),
                array("name" => "rightanswer", "value" => 1),
                array("name" => "overallfeedback", "value" => 0),
                array("name" => "marks", "value" => 2),
            ),
            "warnings" => [],
        );

        $this->assertEquals($expected, $result);

                $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_finish($timenow, false);

        $expected = array(
            "someoptions" => array(
                array("name" => "feedback", "value" => 1),
                array("name" => "generalfeedback", "value" => 1),
                array("name" => "rightanswer", "value" => 1),
                array("name" => "overallfeedback", "value" => 1),
                array("name" => "marks", "value" => 2),
            ),
            "alloptions" => array(
                array("name" => "feedback", "value" => 1),
                array("name" => "generalfeedback", "value" => 1),
                array("name" => "rightanswer", "value" => 1),
                array("name" => "overallfeedback", "value" => 1),
                array("name" => "marks", "value" => 2),
            ),
            "warnings" => [],
        );

                $result = mod_quiz_external::get_combined_review_options($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_combined_review_options_returns(), $result);
        $this->assertEquals($expected, $result);

                $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 2, false, $timenow, false, $this->student->id);
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $expected = array(
            "someoptions" => array(
                array("name" => "feedback", "value" => 1),
                array("name" => "generalfeedback", "value" => 1),
                array("name" => "rightanswer", "value" => 1),
                array("name" => "overallfeedback", "value" => 1),
                array("name" => "marks", "value" => 2),
            ),
            "alloptions" => array(
                array("name" => "feedback", "value" => 1),
                array("name" => "generalfeedback", "value" => 1),
                array("name" => "rightanswer", "value" => 1),
                array("name" => "overallfeedback", "value" => 0),
                array("name" => "marks", "value" => 2),
            ),
            "warnings" => [],
        );

        $result = mod_quiz_external::get_combined_review_options($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_combined_review_options_returns(), $result);
        $this->assertEquals($expected, $result);

                $this->setUser($this->teacher);

        $result = mod_quiz_external::get_combined_review_options($quiz->id, $this->student->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_combined_review_options_returns(), $result);

        $this->assertEquals($expected, $result);

                try {
            mod_quiz_external::get_combined_review_options($quiz->id, -1);
            $this->fail('Exception expected due to missing capability.');
        } catch (dml_missing_record_exception $e) {
            $this->assertEquals('invaliduser', $e->errorcode);
        }
    }

    
    public function test_start_attempt() {
        global $DB;

                list($quiz, $context, $quizobj) = $this->create_quiz_with_questions();

        $this->setUser($this->student);

                $quiz->timeopen = time() - WEEKSECS;
        $quiz->timeclose = time() - DAYSECS;
        $DB->update_record('quiz', $quiz);
        $result = mod_quiz_external::start_attempt($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::start_attempt_returns(), $result);

        $this->assertEquals([], $result['attempt']);
        $this->assertCount(1, $result['warnings']);

                $quiz->timeopen = 0;
        $quiz->timeclose = 0;
        $quiz->password = 'abc';
        $DB->update_record('quiz', $quiz);

        try {
            mod_quiz_external::start_attempt($quiz->id, array(array("name" => "quizpassword", "value" => 'bad')));
            $this->fail('Exception expected due to invalid passwod.');
        } catch (moodle_exception $e) {
            $this->assertEquals(get_string('passworderror', 'quizaccess_password'), $e->errorcode);
        }

                $result = mod_quiz_external::start_attempt($quiz->id, array(array("name" => "quizpassword", "value" => 'abc')));
        $result = external_api::clean_returnvalue(mod_quiz_external::start_attempt_returns(), $result);

        $this->assertEquals(1, $result['attempt']['attempt']);
        $this->assertEquals($this->student->id, $result['attempt']['userid']);
        $this->assertEquals($quiz->id, $result['attempt']['quiz']);
        $this->assertCount(0, $result['warnings']);
        $attemptid = $result['attempt']['id'];

        
        try {
            mod_quiz_external::start_attempt($quiz->id, array(array("name" => "quizpassword", "value" => 'abc')));
            $this->fail('Exception expected due to attempt not finished.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('attemptstillinprogress', $e->errorcode);
        }

        
                $timenow = time();
        $attemptobj = quiz_attempt::create($attemptid);
        $tosubmit = array(1 => array('answer' => '3.14'));
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

                $attemptobj = quiz_attempt::create($attemptid);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);

                $result = mod_quiz_external::start_attempt($quiz->id, array(array("name" => "quizpassword", "value" => 'abc')));
        $result = external_api::clean_returnvalue(mod_quiz_external::start_attempt_returns(), $result);

        $this->assertEquals(2, $result['attempt']['attempt']);
        $this->assertEquals($this->student->id, $result['attempt']['userid']);
        $this->assertEquals($quiz->id, $result['attempt']['quiz']);
        $this->assertCount(0, $result['warnings']);

                        assign_capability('mod/quiz:attempt', CAP_PROHIBIT, $this->studentrole->id, $context->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            mod_quiz_external::start_attempt($quiz->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (required_capability_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

    }

    
    public function test_validate_attempt() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj) = $this->create_quiz_with_questions(true);

        $this->setUser($this->student);

                try {
            $params = array('attemptid' => -1, 'page' => 0);
            testable_mod_quiz_external::validate_attempt($params);
            $this->fail('Exception expected due to invalid attempt id.');
        } catch (dml_missing_record_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $params = array('attemptid' => $attempt->id, 'page' => 0);
        $result = testable_mod_quiz_external::validate_attempt($params);
        $this->assertEquals($attempt->id, $result[0]->get_attempt()->id);
        $this->assertEquals([], $result[1]);

                $quiz->password = 'abc';
        $DB->update_record('quiz', $quiz);

        try {
            $params = array('attemptid' => $attempt->id, 'page' => 0,
                            'preflightdata' => array(array("name" => "quizpassword", "value" => 'bad')));
            testable_mod_quiz_external::validate_attempt($params);
            $this->fail('Exception expected due to invalid passwod.');
        } catch (moodle_exception $e) {
            $this->assertEquals(get_string('passworderror', 'quizaccess_password'), $e->errorcode);
        }

                $params['preflightdata'][0]['value'] = 'abc';
        $result = testable_mod_quiz_external::validate_attempt($params);
        $this->assertEquals($attempt->id, $result[0]->get_attempt()->id);
        $this->assertEquals([], $result[1]);

                $DB->update_record('quiz', $quiz);
        $params['page'] = 4;
        try {
            testable_mod_quiz_external::validate_attempt($params);
            $this->fail('Exception expected due to page out of range.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('Invalid page number', $e->errorcode);
        }

        $params['page'] = 0;
                $quiz->timeopen = time() - WEEKSECS;
        $quiz->timeclose = time() - DAYSECS;
        $DB->update_record('quiz', $quiz);

                testable_mod_quiz_external::validate_attempt($params, false);

                try {
            testable_mod_quiz_external::validate_attempt($params);
            $this->fail('Exception expected due to passed dates.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('attempterror', $e->errorcode);
        }

                $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_finish(time(), false);

        try {
            testable_mod_quiz_external::validate_attempt($params, false);
            $this->fail('Exception expected due to attempt finished.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('attemptalreadyclosed', $e->errorcode);
        }

                        assign_capability('mod/quiz:attempt', CAP_PROHIBIT, $this->studentrole->id, $context->id);
                accesslib_clear_all_caches_for_unit_testing();
        course_modinfo::clear_instance_cache();

        try {
            testable_mod_quiz_external::validate_attempt($params);
            $this->fail('Exception expected due to missing permissions.');
        } catch (required_capability_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

                $this->setUser($this->teacher);

        $params['page'] = 0;
        try {
            testable_mod_quiz_external::validate_attempt($params);
            $this->fail('Exception expected due to not your attempt.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('notyourattempt', $e->errorcode);
        }
    }

    
    public function test_get_attempt_data() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj) = $this->create_quiz_with_questions(true);

        $quizobj = $attemptobj->get_quizobj();
        $quizobj->preload_questions();
        $quizobj->load_questions();
        $questions = $quizobj->get_questions();

        $this->setUser($this->student);

                $result = mod_quiz_external::get_attempt_data($attempt->id, 0);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_data_returns(), $result);

        $this->assertEquals($attempt, (object) $result['attempt']);
        $this->assertEquals(1, $result['nextpage']);
        $this->assertCount(0, $result['messages']);
        $this->assertCount(1, $result['questions']);
        $this->assertEquals(1, $result['questions'][0]['slot']);
        $this->assertEquals(1, $result['questions'][0]['number']);
        $this->assertEquals('numerical', $result['questions'][0]['type']);
        $this->assertEquals('todo', $result['questions'][0]['state']);
        $this->assertEquals(get_string('notyetanswered', 'question'), $result['questions'][0]['status']);
        $this->assertFalse($result['questions'][0]['flagged']);
        $this->assertEquals(0, $result['questions'][0]['page']);
        $this->assertEmpty($result['questions'][0]['mark']);
        $this->assertEquals(1, $result['questions'][0]['maxmark']);

                $result = mod_quiz_external::get_attempt_data($attempt->id, 1);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_data_returns(), $result);

        $this->assertEquals($attempt, (object) $result['attempt']);
        $this->assertEquals(-1, $result['nextpage']);
        $this->assertCount(0, $result['messages']);
        $this->assertCount(1, $result['questions']);
        $this->assertEquals(2, $result['questions'][0]['slot']);
        $this->assertEquals(2, $result['questions'][0]['number']);
        $this->assertEquals('numerical', $result['questions'][0]['type']);
        $this->assertEquals('todo', $result['questions'][0]['state']);
        $this->assertEquals(get_string('notyetanswered', 'question'), $result['questions'][0]['status']);
        $this->assertFalse($result['questions'][0]['flagged']);
        $this->assertEquals(1, $result['questions'][0]['page']);

                $attemptobj->process_finish(time(), false);

                $quiz->questionsperpage = 4;
        $DB->update_record('quiz', $quiz);
        quiz_repaginate_questions($quiz->id, $quiz->questionsperpage);

                $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 2, false, $timenow, false, $this->student->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

                $result = mod_quiz_external::get_attempt_data($attempt->id, 0);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_data_returns(), $result);
        $this->assertCount(2, $result['questions']);
        $this->assertEquals(-1, $result['nextpage']);

                $found = 0;
        foreach ($questions as $question) {
            foreach ($result['questions'] as $rquestion) {
                if ($rquestion['slot'] == $question->slot) {
                    $this->assertTrue(strpos($rquestion['html'], "qid=$question->id") !== false);
                    $found++;
                }
            }
        }
        $this->assertEquals(2, $found);

    }

    
    public function test_get_attempt_summary() {

                list($quiz, $context, $quizobj, $attempt, $attemptobj) = $this->create_quiz_with_questions(true);

        $this->setUser($this->student);
        $result = mod_quiz_external::get_attempt_summary($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_summary_returns(), $result);

                $this->assertEquals('todo', $result['questions'][0]['state']);
        $this->assertEquals('todo', $result['questions'][1]['state']);
        $this->assertEquals(1, $result['questions'][0]['number']);
        $this->assertEquals(2, $result['questions'][1]['number']);
        $this->assertFalse($result['questions'][0]['flagged']);
        $this->assertFalse($result['questions'][1]['flagged']);
        $this->assertEmpty($result['questions'][0]['mark']);
        $this->assertEmpty($result['questions'][1]['mark']);

                $tosubmit = array(1 => array('answer' => '3.14'));
        $attemptobj->process_submitted_actions(time(), false, $tosubmit);
        $result = mod_quiz_external::get_attempt_summary($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_summary_returns(), $result);

                $this->assertEquals('complete', $result['questions'][0]['state']);
        $this->assertEquals('todo', $result['questions'][1]['state']);
        $this->assertEquals(1, $result['questions'][0]['number']);
        $this->assertEquals(2, $result['questions'][1]['number']);
        $this->assertFalse($result['questions'][0]['flagged']);
        $this->assertFalse($result['questions'][1]['flagged']);
        $this->assertEmpty($result['questions'][0]['mark']);
        $this->assertEmpty($result['questions'][1]['mark']);

    }

    
    public function test_save_attempt() {

                list($quiz, $context, $quizobj, $attempt, $attemptobj, $quba) = $this->create_quiz_with_questions(true);

                $prefix = $quba->get_field_prefix(1);
        $data = array(
            array('name' => 'slots', 'value' => 1),
            array('name' => $prefix . ':sequencecheck',
                    'value' => $attemptobj->get_question_attempt(1)->get_sequence_check_count()),
            array('name' => $prefix . 'answer', 'value' => 1),
        );

        $this->setUser($this->student);

        $result = mod_quiz_external::save_attempt($attempt->id, $data);
        $result = external_api::clean_returnvalue(mod_quiz_external::save_attempt_returns(), $result);
        $this->assertTrue($result['status']);

                $result = mod_quiz_external::get_attempt_summary($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_summary_returns(), $result);

                $this->assertEquals('complete', $result['questions'][0]['state']);
        $this->assertEquals('todo', $result['questions'][1]['state']);
        $this->assertEquals(1, $result['questions'][0]['number']);
        $this->assertEquals(2, $result['questions'][1]['number']);
        $this->assertFalse($result['questions'][0]['flagged']);
        $this->assertFalse($result['questions'][1]['flagged']);
        $this->assertEmpty($result['questions'][0]['mark']);
        $this->assertEmpty($result['questions'][1]['mark']);

                $prefix = $quba->get_field_prefix(2);
        $data = array(
            array('name' => 'slots', 'value' => 2),
            array('name' => $prefix . ':sequencecheck',
                    'value' => $attemptobj->get_question_attempt(1)->get_sequence_check_count()),
            array('name' => $prefix . 'answer', 'value' => 1),
        );

        $result = mod_quiz_external::save_attempt($attempt->id, $data);
        $result = external_api::clean_returnvalue(mod_quiz_external::save_attempt_returns(), $result);
        $this->assertTrue($result['status']);

                $result = mod_quiz_external::get_attempt_summary($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_summary_returns(), $result);

                $this->assertEquals('complete', $result['questions'][0]['state']);
        $this->assertEquals('complete', $result['questions'][1]['state']);

    }

    
    public function test_process_attempt() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj, $quba) = $this->create_quiz_with_questions(true);

                $prefix = $quba->get_field_prefix(1);
        $data = array(
            array('name' => 'slots', 'value' => 1),
            array('name' => $prefix . ':sequencecheck',
                    'value' => $attemptobj->get_question_attempt(1)->get_sequence_check_count()),
            array('name' => $prefix . 'answer', 'value' => 1),
        );

        $this->setUser($this->student);

        $result = mod_quiz_external::process_attempt($attempt->id, $data);
        $result = external_api::clean_returnvalue(mod_quiz_external::process_attempt_returns(), $result);
        $this->assertEquals(quiz_attempt::IN_PROGRESS, $result['state']);

                $result = mod_quiz_external::get_attempt_summary($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_summary_returns(), $result);

                $this->assertEquals('complete', $result['questions'][0]['state']);
        $this->assertEquals('todo', $result['questions'][1]['state']);
        $this->assertEquals(1, $result['questions'][0]['number']);
        $this->assertEquals(2, $result['questions'][1]['number']);
        $this->assertFalse($result['questions'][0]['flagged']);
        $this->assertFalse($result['questions'][1]['flagged']);
        $this->assertEmpty($result['questions'][0]['mark']);
        $this->assertEmpty($result['questions'][1]['mark']);

                $prefix = $quba->get_field_prefix(2);
        $data = array(
            array('name' => 'slots', 'value' => 2),
            array('name' => $prefix . ':sequencecheck',
                    'value' => $attemptobj->get_question_attempt(1)->get_sequence_check_count()),
            array('name' => $prefix . 'answer', 'value' => 1),
            array('name' => $prefix . ':flagged', 'value' => 1),
        );

        $result = mod_quiz_external::process_attempt($attempt->id, $data);
        $result = external_api::clean_returnvalue(mod_quiz_external::process_attempt_returns(), $result);
        $this->assertEquals(quiz_attempt::IN_PROGRESS, $result['state']);

                $result = mod_quiz_external::get_attempt_summary($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_summary_returns(), $result);

                $this->assertEquals('complete', $result['questions'][0]['state']);
        $this->assertEquals('complete', $result['questions'][1]['state']);
        $this->assertFalse($result['questions'][0]['flagged']);
        $this->assertTrue($result['questions'][1]['flagged']);

                $result = mod_quiz_external::process_attempt($attempt->id, array(), true);
        $result = external_api::clean_returnvalue(mod_quiz_external::process_attempt_returns(), $result);
        $this->assertEquals(quiz_attempt::FINISHED, $result['state']);

                $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 2, false, $timenow, false, $this->student->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 2, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

                $quiz->timeclose = $timenow - 10;
        $quiz->graceperiod = 60;
        $quiz->overduehandling = 'graceperiod';
        $DB->update_record('quiz', $quiz);

        $result = mod_quiz_external::process_attempt($attempt->id, array());
        $result = external_api::clean_returnvalue(mod_quiz_external::process_attempt_returns(), $result);
        $this->assertEquals(quiz_attempt::OVERDUE, $result['state']);

                $timenow = time();
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $attempt = quiz_create_attempt($quizobj, 3, 2, $timenow, false, $this->student->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 3, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

                $quiz->timeclose = $timenow - HOURSECS;
        $DB->update_record('quiz', $quiz);

        $result = mod_quiz_external::process_attempt($attempt->id, array());
        $result = external_api::clean_returnvalue(mod_quiz_external::process_attempt_returns(), $result);
        $this->assertEquals(quiz_attempt::ABANDONED, $result['state']);

    }

    
    public function test_validate_attempt_review() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj) = $this->create_quiz_with_questions(true);

        $this->setUser($this->student);

                try {
            $params = array('attemptid' => -1);
            testable_mod_quiz_external::validate_attempt_review($params);
            $this->fail('Exception expected due invalid id.');
        } catch (dml_missing_record_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                try {
            $params = array('attemptid' => $attempt->id);
            testable_mod_quiz_external::validate_attempt_review($params);
            $this->fail('Exception expected due not closed attempt.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('attemptclosed', $e->errorcode);
        }

                list($quiz, $context, $quizobj, $attempt, $attemptobj) = $this->create_quiz_with_questions(true, true);

        $params = array('attemptid' => $attempt->id);
        testable_mod_quiz_external::validate_attempt_review($params);

                $this->setUser($this->teacher);
        testable_mod_quiz_external::validate_attempt_review($params);

                $anotherstudent = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($anotherstudent->id, $this->course->id, $this->studentrole->id, 'manual');

        $this->setUser($anotherstudent);
        try {
            $params = array('attemptid' => $attempt->id);
            testable_mod_quiz_external::validate_attempt_review($params);
            $this->fail('Exception expected due missing permissions.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('noreviewattempt', $e->errorcode);
        }
    }


    
    public function test_get_attempt_review() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj, $quba) = $this->create_quiz_with_questions(true, true);

                $feedback = new stdClass();
        $feedback->quizid = $quiz->id;
        $feedback->feedbacktext = 'Feedback text 1';
        $feedback->feedbacktextformat = 1;
        $feedback->mingrade = 49;
        $feedback->maxgrade = 100;
        $feedback->id = $DB->insert_record('quiz_feedback', $feedback);

        $feedback->feedbacktext = 'Feedback text 2';
        $feedback->feedbacktextformat = 1;
        $feedback->mingrade = 30;
        $feedback->maxgrade = 48;
        $feedback->id = $DB->insert_record('quiz_feedback', $feedback);

        $result = mod_quiz_external::get_attempt_review($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_review_returns(), $result);

                $this->assertEquals(50, $result['grade']);
        $this->assertEquals(1, $result['attempt']['attempt']);
        $this->assertEquals('finished', $result['attempt']['state']);
        $this->assertEquals(1, $result['attempt']['sumgrades']);
        $this->assertCount(2, $result['questions']);
        $this->assertEquals('gradedright', $result['questions'][0]['state']);
        $this->assertEquals(1, $result['questions'][0]['slot']);
        $this->assertEquals('gaveup', $result['questions'][1]['state']);
        $this->assertEquals(2, $result['questions'][1]['slot']);

        $this->assertCount(1, $result['additionaldata']);
        $this->assertEquals('feedback', $result['additionaldata'][0]['id']);
        $this->assertEquals('Feedback', $result['additionaldata'][0]['title']);
        $this->assertEquals('Feedback text 1', $result['additionaldata'][0]['content']);

                $result = mod_quiz_external::get_attempt_review($attempt->id, 0);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_review_returns(), $result);

        $this->assertEquals(50, $result['grade']);
        $this->assertEquals(1, $result['attempt']['attempt']);
        $this->assertEquals('finished', $result['attempt']['state']);
        $this->assertEquals(1, $result['attempt']['sumgrades']);
        $this->assertCount(1, $result['questions']);
        $this->assertEquals('gradedright', $result['questions'][0]['state']);
        $this->assertEquals(1, $result['questions'][0]['slot']);

         $this->assertCount(1, $result['additionaldata']);
        $this->assertEquals('feedback', $result['additionaldata'][0]['id']);
        $this->assertEquals('Feedback', $result['additionaldata'][0]['title']);
        $this->assertEquals('Feedback text 1', $result['additionaldata'][0]['content']);

    }

    
    public function test_view_attempt() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj, $quba) = $this->create_quiz_with_questions(true, false);

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_quiz_external::view_attempt($attempt->id, 0);
        $result = external_api::clean_returnvalue(mod_quiz_external::view_attempt_returns(), $result);
        $this->assertTrue($result['status']);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_quiz\event\attempt_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $DB->set_field('quiz', 'navmethod', QUIZ_NAVMETHOD_SEQ, array('id' => $quiz->id));
                $DB->set_field('quiz', 'password', 'abcdef', array('id' => $quiz->id));
        $preflightdata = array(array("name" => "quizpassword", "value" => 'abcdef'));

                $result = mod_quiz_external::view_attempt($attempt->id, 1, $preflightdata);
        $result = external_api::clean_returnvalue(mod_quiz_external::view_attempt_returns(), $result);
        $this->assertTrue($result['status']);

        $events = $sink->get_events();
        $this->assertCount(2, $events);

                try {
            mod_quiz_external::view_attempt($attempt->id, 0);
            $this->fail('Exception expected due to try to see a previous page.');
        } catch (moodle_quiz_exception $e) {
            $this->assertEquals('Out of sequence access', $e->errorcode);
        }

    }

    
    public function test_view_attempt_summary() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj, $quba) = $this->create_quiz_with_questions(true, false);

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_quiz_external::view_attempt_summary($attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::view_attempt_summary_returns(), $result);
        $this->assertTrue($result['status']);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_quiz\event\attempt_summary_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodlequiz = new \moodle_url('/mod/quiz/summary.php', array('attempt' => $attempt->id));
        $this->assertEquals($moodlequiz, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $DB->set_field('quiz', 'password', 'abcdef', array('id' => $quiz->id));
        $preflightdata = array(array("name" => "quizpassword", "value" => 'abcdef'));

        $result = mod_quiz_external::view_attempt_summary($attempt->id, $preflightdata);
        $result = external_api::clean_returnvalue(mod_quiz_external::view_attempt_summary_returns(), $result);
        $this->assertTrue($result['status']);

    }

    
    public function test_view_attempt_review() {
        global $DB;

                list($quiz, $context, $quizobj, $attempt, $attemptobj, $quba) = $this->create_quiz_with_questions(true, true);

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_quiz_external::view_attempt_review($attempt->id, 0);
        $result = external_api::clean_returnvalue(mod_quiz_external::view_attempt_review_returns(), $result);
        $this->assertTrue($result['status']);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_quiz\event\attempt_reviewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodlequiz = new \moodle_url('/mod/quiz/review.php', array('attempt' => $attempt->id));
        $this->assertEquals($moodlequiz, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

    }

    
    public function test_get_quiz_feedback_for_grade() {
        global $DB;

                $feedback = new stdClass();
        $feedback->quizid = $this->quiz->id;
        $feedback->feedbacktext = 'Feedback text 1';
        $feedback->feedbacktextformat = 1;
        $feedback->mingrade = 49;
        $feedback->maxgrade = 100;
        $feedback->id = $DB->insert_record('quiz_feedback', $feedback);

        $feedback->feedbacktext = 'Feedback text 2';
        $feedback->feedbacktextformat = 1;
        $feedback->mingrade = 30;
        $feedback->maxgrade = 49;
        $feedback->id = $DB->insert_record('quiz_feedback', $feedback);

        $result = mod_quiz_external::get_quiz_feedback_for_grade($this->quiz->id, 50);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_quiz_feedback_for_grade_returns(), $result);
        $this->assertEquals('Feedback text 1', $result['feedbacktext']);
        $this->assertEquals(FORMAT_HTML, $result['feedbacktextformat']);

        $result = mod_quiz_external::get_quiz_feedback_for_grade($this->quiz->id, 30);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_quiz_feedback_for_grade_returns(), $result);
        $this->assertEquals('Feedback text 2', $result['feedbacktext']);
        $this->assertEquals(FORMAT_HTML, $result['feedbacktextformat']);

        $result = mod_quiz_external::get_quiz_feedback_for_grade($this->quiz->id, 10);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_quiz_feedback_for_grade_returns(), $result);
        $this->assertEquals('', $result['feedbacktext']);
        $this->assertEquals(FORMAT_MOODLE, $result['feedbacktextformat']);
    }

    
    public function test_get_quiz_access_information() {
        global $DB;

                $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $data = array('course' => $this->course->id);
        $quiz = $quizgenerator->create_instance($data);

        $this->setUser($this->student);

                $result = mod_quiz_external::get_quiz_access_information($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_quiz_access_information_returns(), $result);

        $expected = array(
            'canattempt' => true,
            'canmanage' => false,
            'canpreview' => false,
            'canreviewmyattempts' => true,
            'canviewreports' => false,
            'accessrules' => [],
                        'activerulenames' => ['quizaccess_openclosedate'],
            'preventaccessreasons' => [],
            'warnings' => []
        );

        $this->assertEquals($expected, $result);

                $this->setUser($this->teacher);
        $result = mod_quiz_external::get_quiz_access_information($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_quiz_access_information_returns(), $result);

        $expected['canmanage'] = true;
        $expected['canpreview'] = true;
        $expected['canviewreports'] = true;
        $expected['canattempt'] = false;
        $expected['canreviewmyattempts'] = false;

        $this->assertEquals($expected, $result);

        $this->setUser($this->student);
                $quiz->timeopen = time() + DAYSECS;
        $quiz->timeclose = time() + WEEKSECS;
        $quiz->password = '123456';
        $DB->update_record('quiz', $quiz);

        $result = mod_quiz_external::get_quiz_access_information($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_quiz_access_information_returns(), $result);

                $this->assertCount(3, $result['accessrules']);
                $this->assertCount(2, $result['activerulenames']);
        $this->assertCount(1, $result['preventaccessreasons']);

    }

    
    public function test_get_attempt_access_information() {
        global $DB;

                $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $data = array('course' => $this->course->id,
                      'sumgrades' => 2);
        $quiz = $quizgenerator->create_instance($data);

                $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

        $question = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

                $question = $questiongenerator->create_question('truefalse', null, array('category' => $cat->id));
        $question = $questiongenerator->create_question('essay', null, array('category' => $cat->id));

        $question = $questiongenerator->create_question('random', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

        $quizobj = quiz::create($quiz->id, $this->student->id);

                $item = grade_item::fetch(array('courseid' => $this->course->id, 'itemtype' => 'mod',
                                        'itemmodule' => 'quiz', 'iteminstance' => $quiz->id, 'outcomeid' => null));
        $item->gradepass = 80;
        $item->update();

        $this->setUser($this->student);

                $result = mod_quiz_external::get_attempt_access_information($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_access_information_returns(), $result);

        $expected = array(
            'isfinished' => false,
            'preventnewattemptreasons' => [],
            'warnings' => []
        );

        $this->assertEquals($expected, $result);

                $quiz->attempts = 1;
        $DB->update_record('quiz', $quiz);

                $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $this->student->id);
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

                $attemptobj = quiz_attempt::create($attempt->id);
        $tosubmit = array(1 => array('answer' => '3.14'));
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

                $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);

                $result = mod_quiz_external::get_attempt_access_information($quiz->id, $attempt->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_attempt_access_information_returns(), $result);

                $this->assertCount(1, $result['preventnewattemptreasons']);
        $this->assertFalse($result['ispreflightcheckrequired']);
        $this->assertEquals(get_string('nomoreattempts', 'quiz'), $result['preventnewattemptreasons'][0]);

    }

    
    public function test_get_quiz_required_qtypes() {
        global $DB;

                $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $data = array('course' => $this->course->id);
        $quiz = $quizgenerator->create_instance($data);

                $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

        $question = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

                $question = $questiongenerator->create_question('truefalse', null, array('category' => $cat->id));
        $question = $questiongenerator->create_question('essay', null, array('category' => $cat->id));

        $question = $questiongenerator->create_question('random', null, array('category' => $cat->id));
        quiz_add_quiz_question($question->id, $quiz);

        $this->setUser($this->student);

        $result = mod_quiz_external::get_quiz_required_qtypes($quiz->id);
        $result = external_api::clean_returnvalue(mod_quiz_external::get_quiz_required_qtypes_returns(), $result);

        $expected = array(
            'questiontypes' => ['essay', 'numerical', 'random', 'shortanswer', 'truefalse'],
            'warnings' => []
        );

        $this->assertEquals($expected, $result);

    }
}
