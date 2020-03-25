<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class mod_book_external_testcase extends externallib_advanced_testcase {

    
    public function test_view_book() {
        global $DB;

        $this->resetAfterTest(true);

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $chapter = $bookgenerator->create_chapter(array('bookid' => $book->id));
        $chapterhidden = $bookgenerator->create_chapter(array('bookid' => $book->id, 'hidden' => 1));

        $context = context_module::instance($book->cmid);
        $cm = get_coursemodule_from_instance('book', $book->id);

                try {
            mod_book_external::view_book(0);
            $this->fail('Exception expected due to invalid mod_book instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_book_external::view_book($book->id, 0);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

                $sink = $this->redirectEvents();

        $result = mod_book_external::view_book($book->id, 0);
        $result = external_api::clean_returnvalue(mod_book_external::view_book_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(2, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_book\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/book/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        $event = array_shift($events);
        $this->assertInstanceOf('\mod_book\event\chapter_viewed', $event);
        $this->assertEquals($chapter->id, $event->objectid);

        $result = mod_book_external::view_book($book->id, $chapter->id);
        $result = external_api::clean_returnvalue(mod_book_external::view_book_returns(), $result);

        $events = $sink->get_events();
                $this->assertCount(3, $events);

                try {
            mod_book_external::view_book($book->id, $chapterhidden->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('errorchapter', $e->errorcode);
        }

                        assign_capability('mod/book:read', CAP_PROHIBIT, $studentrole->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_book_external::view_book($book->id, 0);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }

    }

    
    public function test_get_books_by_courses() {
        global $DB, $USER;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $course1 = self::getDataGenerator()->create_course();
        $bookoptions1 = array(
                              'course' => $course1->id,
                              'name' => 'First Book'
                             );
        $book1 = self::getDataGenerator()->create_module('book', $bookoptions1);
        $course2 = self::getDataGenerator()->create_course();
        $bookoptions2 = array(
                              'course' => $course2->id,
                              'name' => 'Second Book'
                             );
        $book2 = self::getDataGenerator()->create_module('book', $bookoptions2);
        $student1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                self::getDataGenerator()->enrol_user($student1->id,  $course1->id, $studentrole->id);
        $this->setUser($student1);

        $books = mod_book_external::get_books_by_courses();
                $books = external_api::clean_returnvalue(mod_book_external::get_books_by_courses_returns(), $books);
        $this->assertCount(1, $books['books']);
        $this->assertEquals('First Book', $books['books'][0]['name']);
                $this->assertCount(9, $books['books'][0]);

                $this->assertFalse(isset($books['books'][0]['section']));

                $books = mod_book_external::get_books_by_courses(array($course2->id));
                $books = external_api::clean_returnvalue(mod_book_external::get_books_by_courses_returns(), $books);
        $this->assertCount(0, $books['books']);
        $this->assertEquals(1, $books['warnings'][0]['warningcode']);

                $this->setAdminUser();
                $books = mod_book_external::get_books_by_courses(array($course2->id));
                $books = external_api::clean_returnvalue(mod_book_external::get_books_by_courses_returns(), $books);

        $this->assertCount(1, $books['books']);
        $this->assertEquals('Second Book', $books['books'][0]['name']);
                $this->assertCount(16, $books['books'][0]);
                $this->assertEquals(0, $books['books'][0]['section']);

                self::getDataGenerator()->enrol_user($student1->id,  $course2->id, $studentrole->id);
        $this->setUser($student1);
        $books = mod_book_external::get_books_by_courses();
        $books = external_api::clean_returnvalue(mod_book_external::get_books_by_courses_returns(), $books);
        $this->assertCount(2, $books['books']);

    }
}
