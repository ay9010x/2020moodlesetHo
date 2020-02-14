<?php



defined('MOODLE_INTERNAL') || die();


class report_stats_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->setAdminUser();
        $this->resetAfterTest();
    }

    
    public function test_report_viewed() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

                $event = \report_stats\event\report_viewed::create(array('context' => $context, 'relateduserid' => $user->id,
                'other' => array('report' => 0, 'time' => 0, 'mode' => 1)));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\report_stats\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, "course", "report stats", "report/stats/index.php?course=$course->id", $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_user_report_viewed() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

                $event = \report_stats\event\user_report_viewed::create(array('context' => $context, 'relateduserid' => $user->id));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\report_stats\event\user_report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = $url = 'report/stats/user.php?id=' . $user->id . '&course=' . $course->id;
        $expected = array($course->id, 'course', 'report stats', $url, $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
