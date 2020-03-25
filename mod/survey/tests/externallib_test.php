<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/survey/lib.php');


class mod_survey_external_testcase extends externallib_advanced_testcase {

    
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

                $this->course = $this->getDataGenerator()->create_course();
        $this->survey = $this->getDataGenerator()->create_module('survey', array('course' => $this->course->id));
        $this->context = context_module::instance($this->survey->cmid);
        $this->cm = get_coursemodule_from_instance('survey', $this->survey->id);

                $this->student = self::getDataGenerator()->create_user();
        $this->teacher = self::getDataGenerator()->create_user();

                $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $this->studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $this->teacherrole->id, 'manual');
    }


    
    public function test_mod_survey_get_surveys_by_courses() {
        global $DB;

                $course2 = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course2->id;
        $survey2 = self::getDataGenerator()->create_module('survey', $record);
                $DB->set_field('survey', 'intro', '', array('id' => $survey2->id));

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

        $returndescription = mod_survey_external::get_surveys_by_courses_returns();

                        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'intro', 'introformat', 'template', 'days', 'questions',
                                    'surveydone');

                $survey1 = $this->survey;
        $survey1->coursemodule = $survey1->cmid;
        $survey1->introformat = 1;
        $survey1->surveydone = 0;
        $survey1->section = 0;
        $survey1->visible = true;
        $survey1->groupmode = 0;
        $survey1->groupingid = 0;

        $survey2->coursemodule = $survey2->cmid;
        $survey2->introformat = 1;
        $survey2->surveydone = 0;
        $survey2->section = 0;
        $survey2->visible = true;
        $survey2->groupmode = 0;
        $survey2->groupingid = 0;
        $tempo = $DB->get_field("survey", "intro", array("id" => $survey2->template));
        $survey2->intro = nl2br(get_string($tempo, "survey"));

        foreach ($expectedfields as $field) {
            $expected1[$field] = $survey1->{$field};
            $expected2[$field] = $survey2->{$field};
        }

        $expectedsurveys = array($expected2, $expected1);

                $result = mod_survey_external::get_surveys_by_courses(array($course2->id, $this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);

        $this->assertEquals($expectedsurveys, $result['surveys']);
        $this->assertCount(0, $result['warnings']);

                $result = mod_survey_external::get_surveys_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedsurveys, $result['surveys']);
        $this->assertCount(0, $result['warnings']);

                $enrol->unenrol_user($instance2, $this->student->id);
        array_shift($expectedsurveys);

                $result = mod_survey_external::get_surveys_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedsurveys, $result['surveys']);

                $result = mod_survey_external::get_surveys_by_courses(array($course2->id));
        $this->assertCount(1, $result['warnings']);
        $this->assertEquals('1', $result['warnings'][0]['warningcode']);
        $this->assertEquals($course2->id, $result['warnings'][0]['itemid']);

                self::setUser($this->teacher);

        $additionalfields = array('timecreated', 'timemodified', 'section', 'visible', 'groupmode', 'groupingid');

        foreach ($additionalfields as $field) {
            $expectedsurveys[0][$field] = $survey1->{$field};
        }

        $result = mod_survey_external::get_surveys_by_courses();
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedsurveys, $result['surveys']);

                self::setAdminUser();

        $result = mod_survey_external::get_surveys_by_courses(array($this->course->id));
        $result = external_api::clean_returnvalue($returndescription, $result);
        $this->assertEquals($expectedsurveys, $result['surveys']);

                $this->setUser($this->student);
        $contextcourse1 = context_course::instance($this->course->id);
                assign_capability('mod/survey:participate', CAP_PROHIBIT, $this->studentrole->id, $contextcourse1->id);
        accesslib_clear_all_caches_for_unit_testing();

        $surveys = mod_survey_external::get_surveys_by_courses(array($this->course->id));
        $surveys = external_api::clean_returnvalue(mod_survey_external::get_surveys_by_courses_returns(), $surveys);
        $this->assertFalse(isset($surveys['surveys'][0]['intro']));
    }

    
    public function test_view_survey() {
        global $DB;

                try {
            mod_survey_external::view_survey(0);
            $this->fail('Exception expected due to invalid mod_survey instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);
        try {
            mod_survey_external::view_survey($this->survey->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $this->setUser($this->student);

                $sink = $this->redirectEvents();

        $result = mod_survey_external::view_survey($this->survey->id);
        $result = external_api::clean_returnvalue(mod_survey_external::view_survey_returns(), $result);
        $this->assertTrue($result['status']);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_survey\event\course_module_viewed', $event);
        $this->assertEquals($this->context, $event->get_context());
        $moodlesurvey = new \moodle_url('/mod/survey/view.php', array('id' => $this->cm->id));
        $this->assertEquals($moodlesurvey, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/survey:participate', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_survey_external::view_survey($this->survey->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

    }

    
    public function test_get_questions() {
        global $DB;

                $this->setUser($this->student);

                $expectedquestions = array();
        $questions = survey_get_questions($this->survey);
        foreach ($questions as $q) {
            if ($q->type >= 0) {
                $expectedquestions[$q->id] = $q;
                if ($q->multi) {
                    $subquestions = survey_get_subquestions($q);
                    foreach ($subquestions as $sq) {
                        $expectedquestions[$sq->id] = $sq;
                    }
                }
            }
        }

        $result = mod_survey_external::get_questions($this->survey->id);
        $result = external_api::clean_returnvalue(mod_survey_external::get_questions_returns(), $result);

                $this->assertCount(0, $result['warnings']);
        foreach ($result['questions'] as $q) {
            $this->assertEquals(get_string($expectedquestions[$q['id']]->text, 'survey'), $q['text']);
            $this->assertEquals(get_string($expectedquestions[$q['id']]->shorttext, 'survey'), $q['shorttext']);
            $this->assertEquals($expectedquestions[$q['id']]->multi, $q['multi']);
            $this->assertEquals($expectedquestions[$q['id']]->type, $q['type']);
                        if ($q['multi']) {
                $this->assertEquals(0, $q['parent']);
                $this->assertEquals(get_string($expectedquestions[$q['id']]->options, 'survey'), $q['options']);
            }
        }

                        assign_capability('mod/survey:participate', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_survey_external::get_questions($this->survey->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }
    }

    
    public function test_submit_answers() {
        global $DB;

                $this->setUser($this->student);

                $realquestions = array();
        $questions = survey_get_questions($this->survey);
        $i = 5;
        foreach ($questions as $q) {
            if ($q->type >= 0) {
                if ($q->multi) {
                    $subquestions = survey_get_subquestions($q);
                    foreach ($subquestions as $sq) {
                        $realquestions[] = array(
                            'key' => 'q' . $sq->id,
                            'value' => $i % 5 + 1                           );
                        $i++;
                    }
                } else {
                    $realquestions[] = array(
                        'key' => 'q' . $q->id,
                        'value' => $i % 5 + 1
                    );
                    $i++;
                }
            }
        }

        $result = mod_survey_external::submit_answers($this->survey->id, $realquestions);
        $result = external_api::clean_returnvalue(mod_survey_external::submit_answers_returns(), $result);

        $this->assertTrue($result['status']);
        $this->assertCount(0, $result['warnings']);

        $dbanswers = $DB->get_records_menu('survey_answers', array('survey' => $this->survey->id), '', 'question, answer1');
        foreach ($realquestions as $q) {
            $id = str_replace('q', '', $q['key']);
            $this->assertEquals($q['value'], $dbanswers[$id]);
        }

                try {
            mod_survey_external::submit_answers($this->survey->id, $realquestions);
            $this->fail('Exception expected due to answers already submitted.');
        } catch (moodle_exception $e) {
            $this->assertEquals('alreadysubmitted', $e->errorcode);
        }

                        assign_capability('mod/survey:participate', CAP_PROHIBIT, $this->studentrole->id, $this->context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_survey_external::submit_answers($this->survey->id, $realquestions);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

                $usernotenrolled = self::getDataGenerator()->create_user();
        $this->setUser($usernotenrolled);
        try {
            mod_survey_external::submit_answers($this->survey->id, $realquestions);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }
    }

}
