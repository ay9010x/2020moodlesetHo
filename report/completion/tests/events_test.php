<?php



defined('MOODLE_INTERNAL') || die();


class report_completion_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->setAdminUser();
        $this->resetAfterTest();
    }

    
    public function test_report_viewed() {
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
                $event = \report_completion\event\report_viewed::create(array('context' => $context));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\report_completion\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new moodle_url('/report/completion/index.php', array('course' => $course->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_user_report_viewed() {
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
                $event = \report_completion\event\user_report_viewed::create(array('context' => $context, 'relateduserid' => 3));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\report_completion\event\user_report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals(3, $event->relateduserid);
        $this->assertEquals(new moodle_url('/report/completion/user.php', array('id' => 3, 'course' => $course->id)),
                $event->get_url());
        $expected = array($course->id, 'course', 'report completion', "report/completion/user.php?id=3&course=$course->id",
                $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
