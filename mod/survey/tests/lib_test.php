<?php



defined('MOODLE_INTERNAL') || die();



class mod_survey_lib_testcase extends advanced_testcase {

    
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/survey/lib.php');
    }

    
    public function test_survey_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $survey = $this->getDataGenerator()->create_module('survey', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($survey->cmid);
        $cm = get_coursemodule_from_instance('survey', $survey->id);

                $sink = $this->redirectEvents();

        survey_view($survey, $course, $cm, $context, 'form');

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_survey\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/survey/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEquals('form', $event->other['viewed']);
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }

    
    public function test_survey_order_questions() {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $survey = $this->getDataGenerator()->create_module('survey', array('course' => $course->id));

        $orderedquestionids = explode(',', $survey->questions);
        $surveyquestions = $DB->get_records_list("survey_questions", "id", $orderedquestionids);

        $questionsordered = survey_order_questions($surveyquestions, $orderedquestionids);

                for ($i = 0; $i < count($orderedquestionids); $i++) {
            $this->assertEquals($orderedquestionids[$i], $questionsordered[$i]->id);
        }
    }

    
    public function test_survey_save_answers() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

                $course = $this->getDataGenerator()->create_course();
        $survey = $this->getDataGenerator()->create_module('survey', array('course' => $course->id));
        $context = context_module::instance($survey->cmid);

                $realquestions = array();
        $questions = survey_get_questions($survey);
        $i = 5;
        foreach ($questions as $q) {
            if ($q->type > 0) {
                if ($q->multi) {
                    $subquestions = survey_get_subquestions($q);
                    foreach ($subquestions as $sq) {
                        $key = 'q' . $sq->id;
                        $realquestions[$key] = $i % 5 + 1;
                        $i++;
                    }
                } else {
                    $key = 'q' . $q->id;
                    $realquestions[$key] = $i % 5 + 1;
                    $i++;
                }
            }
        }

        $sink = $this->redirectEvents();
        survey_save_answers($survey, $realquestions, $course, $context);

                $dbanswers = $DB->get_records_menu('survey_answers', array('survey' => $survey->id), '', 'question, answer1');
        foreach ($realquestions as $key => $value) {
            $id = str_replace('q', '', $key);
            $this->assertEquals($value, $dbanswers[$id]);
        }

                $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_survey\event\response_submitted', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($survey->id, $event->other['surveyid']);
    }
}
