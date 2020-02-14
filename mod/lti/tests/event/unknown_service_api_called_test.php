<?php



defined('MOODLE_INTERNAL') || die();

use mod_lti\event\unknown_service_api_called;


class mod_lti_event_unknown_service_api_called_test extends advanced_testcase {
    
    public function test_create_event() {
        $event = unknown_service_api_called::create();
        $this->assertInstanceOf('\mod_lti\event\unknown_service_api_called', $event);
    }

    
    public function test_event_context() {
        $event = unknown_service_api_called::create();
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    
    public function test_trigger_event() {
        $event = unknown_service_api_called::create();

        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
    }

    
    public function test_get_message_data() {
        $data = (object) array(
            'foo' => 'bar',
            'bat' => 'baz',
        );

        
        $event = unknown_service_api_called::create();
        $event->set_message_data($data);
        $this->assertSame($data, $event->get_message_data());
    }
}
