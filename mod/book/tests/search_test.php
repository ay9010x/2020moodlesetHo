<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');


class mod_book_search_testcase extends advanced_testcase {

    
    protected $bookchapterareaid = null;

    public function setUp() {
        $this->resetAfterTest(true);
        set_config('enableglobalsearch', true);

        $this->bookchapterareaid = \core_search\manager::generate_areaid('mod_book', 'chapter');

                $search = testable_core_search::instance();
    }

    
    public function test_search_enabled() {

        $searcharea = \core_search\manager::get_search_area($this->bookchapterareaid);
        list($componentname, $varname) = $searcharea->get_config_var_name();

                $this->assertTrue($searcharea->is_enabled());

        set_config($varname . '_enabled', false, $componentname);
        $this->assertFalse($searcharea->is_enabled());

        set_config($varname . '_enabled', true, $componentname);
        $this->assertTrue($searcharea->is_enabled());
    }

    
    public function test_chapters_indexing() {
        global $DB;

                $searcharea = \core_search\manager::get_search_area($this->bookchapterareaid);
        $this->assertInstanceOf('\mod_book\search\chapter', $searcharea);

        $course1 = self::getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course1->id));

        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $chapter1 = $bookgenerator->create_chapter(array('bookid' => $book->id, 'content' => 'Chapter1', 'title' => 'Title1'));
        $chapter2 = $bookgenerator->create_chapter(array('bookid' => $book->id, 'content' => 'Chapter2', 'title' => 'Title2'));

                $recordset = $searcharea->get_recordset_by_timestamp(0);
        $this->assertTrue($recordset->valid());
        $nrecords = 0;
        foreach ($recordset as $record) {
            $this->assertInstanceOf('stdClass', $record);
            $doc = $searcharea->get_document($record);
            $this->assertInstanceOf('\core_search\document', $doc);

                        $dbreads = $DB->perf_get_reads();
            $doc = $searcharea->get_document($record);
            $this->assertEquals($dbreads, $DB->perf_get_reads());
            $this->assertInstanceOf('\core_search\document', $doc);
            $nrecords++;
        }
                $recordset->close();
        $this->assertEquals(2, $nrecords);

                $recordset = $searcharea->get_recordset_by_timestamp(time() + 2);

                $this->assertFalse($recordset->valid());
        $recordset->close();
    }

    
    public function test_check_access() {
        global $DB;

                $searcharea = \core_search\manager::get_search_area($this->bookchapterareaid);
        $this->assertInstanceOf('\mod_book\search\chapter', $searcharea);

        $user1 = self::getDataGenerator()->create_user();
        $course1 = self::getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        $book = $this->getDataGenerator()->create_module('book', array('course' => $course1->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');

        $chapter = array('bookid' => $book->id, 'content' => 'Chapter1', 'title' => 'Title1');
        $chapter1 = $bookgenerator->create_chapter($chapter);
        $chapter['content'] = 'Chapter2';
        $chapter['title'] = 'Title2';
        $chapter['hidden'] = 1;
        $chapter2 = $bookgenerator->create_chapter($chapter);

        $this->setAdminUser();
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($chapter1->id));
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($chapter2->id));

        $this->setUser($user1);

        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($chapter1->id));
        $this->assertEquals(\core_search\manager::ACCESS_DENIED, $searcharea->check_access($chapter2->id));

        $this->assertEquals(\core_search\manager::ACCESS_DELETED, $searcharea->check_access($chapter2->id + 10));
    }
}
