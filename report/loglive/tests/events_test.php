<?php



defined('MOODLE_INTERNAL') || die();


class report_loglive_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->setAdminUser();
        $this->resetAfterTest();
    }

    
    public function test_report_viewed() {
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
                $event = \report_loglive\event\report_viewed::create(array('context' => $context));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\report_loglive\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'course', 'report live', "report/loglive/index.php?id=$course->id", $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/report/loglive/index.php', array('id' => $course->id));
        $this->assertEquals($url, $event->get_url());
    }
}
