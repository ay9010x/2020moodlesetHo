<?php



defined('MOODLE_INTERNAL') || die();
global $CFG;


class booktool_exportimscp_events_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest();
    }

    public function test_book_exported() {
                
        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $context = context_module::instance($book->cmid);

        $event = \booktool_exportimscp\event\book_exported::create_from_book($book, $context);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\booktool_exportimscp\event\book_exported', $event);
        $this->assertEquals(context_module::instance($book->cmid), $event->get_context());
        $this->assertEquals($book->id, $event->objectid);
        $expected = array($course->id, 'book', 'exportimscp', 'tool/exportimscp/index.php?id=' . $book->cmid,
            $book->id, $book->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

}
