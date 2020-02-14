<?php



defined('MOODLE_INTERNAL') || die();


class mod_survey_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();
    }

    
    public function test_report_downloaded() {
                
        $course = $this->getDataGenerator()->create_course();
        $survey = $this->getDataGenerator()->create_module('survey', array('course' => $course->id));

        $params = array(
            'objectid' => $survey->id,
            'context' => context_module::instance($survey->cmid),
            'courseid' => $course->id,
            'other' => array('type' => 'xls')
        );
        $event = \mod_survey\event\report_downloaded::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_survey\event\report_downloaded', $event);
        $this->assertEquals(context_module::instance($survey->cmid), $event->get_context());
        $this->assertEquals($survey->id, $event->objectid);
        $url = new moodle_url('/mod/survey/download.php', array('id' => $survey->cmid, 'type' => 'xls'));
        $expected = array($course->id, "survey", "download", $url->out(), $survey->id, $survey->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_report_viewed() {
                
        $course = $this->getDataGenerator()->create_course();
        $survey = $this->getDataGenerator()->create_module('survey', array('course' => $course->id));

        $params = array(
            'objectid' => $survey->id,
            'context' => context_module::instance($survey->cmid),
            'courseid' => $course->id
        );
        $event = \mod_survey\event\report_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_survey\event\report_viewed', $event);
        $this->assertEquals(context_module::instance($survey->cmid), $event->get_context());
        $this->assertEquals($survey->id, $event->objectid);
        $expected = array($course->id, "survey", "view report", 'report.php?id=' . $survey->cmid, $survey->id, $survey->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_response_submitted() {
                
        $course = $this->getDataGenerator()->create_course();
        $survey = $this->getDataGenerator()->create_module('survey', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($survey->cmid),
            'courseid' => $course->id,
            'other' => array('surveyid' => $survey->id)
        );
        $event = \mod_survey\event\response_submitted::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_survey\event\response_submitted', $event);
        $this->assertEquals(context_module::instance($survey->cmid), $event->get_context());
        $this->assertEquals($survey->id, $event->other['surveyid']);
        $expected = array($course->id, "survey", "submit", 'view.php?id=' . $survey->cmid, $survey->id, $survey->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
