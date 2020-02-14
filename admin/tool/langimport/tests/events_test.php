<?php



defined('MOODLE_INTERNAL') || die();


class tool_langimport_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->setAdminUser();
        $this->resetAfterTest();
    }

    public function test_langpack_updated() {
        global $CFG;

        $event = \tool_langimport\event\langpack_updated::event_with_langcode($CFG->lang);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\tool_langimport\event\langpack_updated', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    public function test_langpack_updated_validation() {
        $this->setExpectedException('coding_exception', 'The \'langcode\' value must be set to a valid language code');

        \tool_langimport\event\langpack_updated::event_with_langcode('broken langcode');
    }

    public function test_langpack_installed() {
        $event = \tool_langimport\event\langpack_imported::event_with_langcode('fr');

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\tool_langimport\event\langpack_imported', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    public function test_langpack_installed_validation() {
        $this->setExpectedException('coding_exception', 'The \'langcode\' value must be set to a valid language code');

        \tool_langimport\event\langpack_imported::event_with_langcode('broken langcode');
    }

    public function test_langpack_removed() {
        $event = \tool_langimport\event\langpack_removed::event_with_langcode('fr');

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);

        $this->assertInstanceOf('\tool_langimport\event\langpack_removed', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    public function test_langpack_removed_validation() {
        $this->setExpectedException('coding_exception', 'The \'langcode\' value must be set to a valid language code');

        \tool_langimport\event\langpack_removed::event_with_langcode('broken langcode');
    }
}
