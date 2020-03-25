<?php



defined('MOODLE_INTERNAL') || die();


class tool_recyclebin_events_testcase extends advanced_testcase {

    
    public function setUp() {
        $this->resetAfterTest();

                set_config('categorybinenable', 1, 'tool_recyclebin');
        set_config('coursebinenable', 1, 'tool_recyclebin');
    }

    
    public function test_category_bin_item_created() {
                $course = $this->getDataGenerator()->create_course();

                $sink = $this->redirectEvents();
        delete_course($course, false);
        $events = $sink->get_events();
        $event = reset($events);

                $rb = new \tool_recyclebin\category_bin($course->category);
        $items = $rb->get_items();
        $item = reset($items);

                $this->assertInstanceOf('\tooL_recyclebin\event\category_bin_item_created', $event);
        $this->assertEquals(context_coursecat::instance($course->category), $event->get_context());
        $this->assertEquals($item->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_category_bin_item_deleted() {
                $course = $this->getDataGenerator()->create_course();

                delete_course($course, false);

                $rb = new \tool_recyclebin\category_bin($course->category);
        $items = $rb->get_items();
        $item = reset($items);

                $sink = $this->redirectEvents();
        $rb->delete_item($item);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tooL_recyclebin\event\category_bin_item_deleted', $event);
        $this->assertEquals(context_coursecat::instance($course->category), $event->get_context());
        $this->assertEquals($item->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_category_bin_item_restored() {
                $course = $this->getDataGenerator()->create_course();

                delete_course($course, false);

                $rb = new \tool_recyclebin\category_bin($course->category);
        $items = $rb->get_items();
        $item = reset($items);

                $sink = $this->redirectEvents();
        $rb->restore_item($item);
        $events = $sink->get_events();
        $event = $events[6];

                $this->assertInstanceOf('\tooL_recyclebin\event\category_bin_item_restored', $event);
        $this->assertEquals(context_coursecat::instance($course->category), $event->get_context());
        $this->assertEquals($item->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_bin_item_created() {
                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance(array('course' => $course->id));

                $sink = $this->redirectEvents();
        course_delete_module($instance->cmid);
        $events = $sink->get_events();
        $event = reset($events);

                $rb = new \tool_recyclebin\course_bin($course->id);
        $items = $rb->get_items();
        $item = reset($items);

                $this->assertInstanceOf('\tooL_recyclebin\event\course_bin_item_created', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($item->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_bin_item_deleted() {
                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance(array('course' => $course->id));

                course_delete_module($instance->cmid);

                $rb = new \tool_recyclebin\course_bin($course->id);
        $items = $rb->get_items();
        $item = reset($items);

                $sink = $this->redirectEvents();
        $rb->delete_item($item);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tooL_recyclebin\event\course_bin_item_deleted', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($item->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
    }

    
    public function test_course_bin_item_restored() {
                $course = $this->getDataGenerator()->create_course();

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_assign');
        $instance = $generator->create_instance(array('course' => $course->id));

        course_delete_module($instance->cmid);

                $rb = new \tool_recyclebin\course_bin($course->id);
        $items = $rb->get_items();
        $item = reset($items);

                $sink = $this->redirectEvents();
        $rb->restore_item($item);
        $events = $sink->get_events();
        $event = reset($events);

                $this->assertInstanceOf('\tooL_recyclebin\event\course_bin_item_restored', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($item->id, $event->objectid);
        $this->assertEventContextNotUsed($event);
    }
}
