<?php



defined('MOODLE_INTERNAL') || die();


class mod_resource_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();

                $this->setAdminUser();
    }

    
    public function test_course_module_instance_list_viewed() {
                
        $course = $this->getDataGenerator()->create_course();
        $params = array(
            'context' => context_course::instance($course->id)
        );
        $event = \mod_resource\event\course_module_instance_list_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_resource\event\course_module_instance_list_viewed', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $expected = array($course->id, 'resource', 'view all', 'index.php?id='.$course->id, '');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_module_viewed() {
                
        $course = $this->getDataGenerator()->create_course();
        $resource = $this->getDataGenerator()->create_module('resource', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($resource->cmid),
            'objectid' => $resource->id
        );
        $event = \mod_resource\event\course_module_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_resource\event\course_module_viewed', $event);
        $this->assertEquals(context_module::instance($resource->cmid), $event->get_context());
        $this->assertEquals($resource->id, $event->objectid);
        $expected = array($course->id, 'resource', 'view', 'view.php?id=' . $resource->cmid, $resource->id, $resource->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }
}
