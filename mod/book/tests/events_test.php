<?php



defined('MOODLE_INTERNAL') || die();
global $CFG;


class mod_book_events_testcase extends advanced_testcase {

    public function setUp() {
        $this->resetAfterTest();
    }

    public function test_chapter_created() {
                
        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $context = context_module::instance($book->cmid);

        $chapter = $bookgenerator->create_chapter(array('bookid' => $book->id));

        $event = \mod_book\event\chapter_created::create_from_chapter($book, $context, $chapter);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_book\event\chapter_created', $event);
        $this->assertEquals(context_module::instance($book->cmid), $event->get_context());
        $this->assertEquals($chapter->id, $event->objectid);
        $expected = array($course->id, 'book', 'add chapter', 'view.php?id='.$book->cmid.'&chapterid='.$chapter->id,
            $chapter->id, $book->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_chapter_updated() {
                
        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $context = context_module::instance($book->cmid);

        $chapter = $bookgenerator->create_chapter(array('bookid' => $book->id));

        $event = \mod_book\event\chapter_updated::create_from_chapter($book, $context, $chapter);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_book\event\chapter_updated', $event);
        $this->assertEquals(context_module::instance($book->cmid), $event->get_context());
        $this->assertEquals($chapter->id, $event->objectid);
        $expected = array($course->id, 'book', 'update chapter', 'view.php?id='.$book->cmid.'&chapterid='.$chapter->id,
            $chapter->id, $book->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_chapter_deleted() {
                
        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $context = context_module::instance($book->cmid);

        $chapter = $bookgenerator->create_chapter(array('bookid' => $book->id));

        $event = \mod_book\event\chapter_deleted::create_from_chapter($book, $context, $chapter);
        $legacy = array($course->id, 'book', 'update', 'view.php?id='.$book->cmid, $book->id, $book->cmid);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_book\event\chapter_deleted', $event);
        $this->assertEquals(context_module::instance($book->cmid), $event->get_context());
        $this->assertEquals($chapter->id, $event->objectid);
        $this->assertEquals($chapter, $event->get_record_snapshot('book_chapters', $chapter->id));
        $this->assertEventLegacyLogData($legacy, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_course_module_instance_list_viewed() {
                
        $course = $this->getDataGenerator()->create_course();
        $params = array(
            'context' => context_course::instance($course->id)
        );
        $event = \mod_book\event\course_module_instance_list_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_book\event\course_module_instance_list_viewed', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $expected = array($course->id, 'book', 'view all', 'index.php?id='.$course->id, '');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_course_module_viewed() {
                
        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($book->cmid),
            'objectid' => $book->id
        );
        $event = \mod_book\event\course_module_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_book\event\course_module_viewed', $event);
        $this->assertEquals(context_module::instance($book->cmid), $event->get_context());
        $this->assertEquals($book->id, $event->objectid);
        $expected = array($course->id, 'book', 'view', 'view.php?id=' . $book->cmid, $book->id, $book->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_chapter_viewed() {
                
        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $context = context_module::instance($book->cmid);

        $chapter = $bookgenerator->create_chapter(array('bookid' => $book->id));

        $event = \mod_book\event\chapter_viewed::create_from_chapter($book, $context, $chapter);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_book\event\chapter_viewed', $event);
        $this->assertEquals(context_module::instance($book->cmid), $event->get_context());
        $this->assertEquals($chapter->id, $event->objectid);
        $expected = array($course->id, 'book', 'view chapter', 'view.php?id=' . $book->cmid . '&amp;chapterid=' .
            $chapter->id, $chapter->id, $book->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

}
