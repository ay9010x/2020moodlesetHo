<?php



defined('MOODLE_INTERNAL') || die();


class report_log_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->setAdminUser();
        $this->resetAfterTest();
    }

    
    public function test_report_viewed() {
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

                $event = \report_log\event\report_viewed::create(array('context' => $context,
                'relateduserid' => 0, 'other' => array('groupid' => 0, 'date' => 0, 'modid' => 0, 'modaction' => '',
                'logformat' => 'showashtml')));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\report_log\event\report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, "course", "report log", "report/log/index.php?id=$course->id", $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/report/log/index.php', array('id' => $event->courseid));
        $this->assertEquals($url, $event->get_url());
    }

    
    public function test_user_report_viewed() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

                $event = \report_log\event\user_report_viewed::create(array('context' => $context,
                'relateduserid' => $user->id, 'other' => array('mode' => 'today')));

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\report_log\event\user_report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = 'report/log/user.php?id=' . $user->id . '&course=' . $course->id . '&mode=today';
        $expected = array($course->id, "course", "report log", $url, $course->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/report/log/user.php', array('course' => $course->id, 'id' => $user->id, 'mode' => 'today'));
        $this->assertEquals($url, $event->get_url());
    }
}
