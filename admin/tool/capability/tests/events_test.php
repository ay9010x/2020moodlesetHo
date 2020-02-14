<?php



defined('MOODLE_INTERNAL') || die();


class tool_capability_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->setAdminUser();
        $this->resetAfterTest();
    }

    
    public function test_report_viewed() {
        $event = \tool_capability\event\report_viewed::create();

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\tool_capability\event\report_viewed', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
        $expected = array(SITEID, "admin", "tool capability", "tool/capability/index.php");
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/admin/tool/capability/index.php');
        $this->assertEquals($url, $event->get_url());
        $event->get_name();
    }
}
