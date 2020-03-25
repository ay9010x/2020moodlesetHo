<?php




class mod_book_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('book', array('course' => $course->id)));
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('book', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('book', array('course' => $course->id, 'id' => $book->id)));

        $params = array('course' => $course->id, 'name' => 'One more book');
        $book = $this->getDataGenerator()->create_module('book', $params);
        $this->assertEquals(2, $DB->count_records('book', array('course' => $course->id)));
        $this->assertEquals('One more book', $DB->get_field_select('book', 'name', 'id = :id', array('id' => $book->id)));
    }

    public function test_create_chapter() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');

        $this->assertFalse($DB->record_exists('book_chapters', array('bookid' => $book->id)));
        $bookgenerator->create_chapter(array('bookid' => $book->id));
        $this->assertTrue($DB->record_exists('book_chapters', array('bookid' => $book->id)));

        $chapter = $bookgenerator->create_chapter(array('bookid' => $book->id, 'content' => 'Yay!', 'title' => 'Oops'));
        $this->assertEquals(2, $DB->count_records('book_chapters', array('bookid' => $book->id)));
        $this->assertEquals('Oops', $DB->get_field_select('book_chapters', 'title', 'id = :id', array('id' => $chapter->id)));
        $this->assertEquals('Yay!', $DB->get_field_select('book_chapters', 'content', 'id = :id', array('id' => $chapter->id)));

        $chapter = $bookgenerator->create_content($book);
        $this->assertEquals(3, $DB->count_records('book_chapters', array('bookid' => $book->id)));
    }

}
