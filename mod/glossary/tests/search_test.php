<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/mod/glossary/tests/generator/lib.php');


class mod_glossary_search_testcase extends advanced_testcase {

    
    protected $entryareaid = null;

    public function setUp() {
        $this->resetAfterTest(true);
        set_config('enableglobalsearch', true);

                $search = testable_core_search::instance();

        $this->entryareaid = \core_search\manager::generate_areaid('mod_glossary', 'entry');
    }

    
    public function test_search_enabled() {

        $searcharea = \core_search\manager::get_search_area($this->entryareaid);
        list($componentname, $varname) = $searcharea->get_config_var_name();

                $this->assertTrue($searcharea->is_enabled());

        set_config($varname . '_enabled', false, $componentname);
        $this->assertFalse($searcharea->is_enabled());

        set_config($varname . '_enabled', true, $componentname);
        $this->assertTrue($searcharea->is_enabled());
    }

    
    public function test_entries_indexing() {
        global $DB;

        $searcharea = \core_search\manager::get_search_area($this->entryareaid);
        $this->assertInstanceOf('\mod_glossary\search\entry', $searcharea);

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, 'student');

        $record = new stdClass();
        $record->course = $course1->id;

        $this->setUser($user1);

                $glossary1 = self::getDataGenerator()->create_module('glossary', $record);
        $entry1 = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary1);
        $entry2 = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary1);

                $recordset = $searcharea->get_recordset_by_timestamp(0);
        $this->assertTrue($recordset->valid());
        $nrecords = 0;
        foreach ($recordset as $record) {
            $this->assertInstanceOf('stdClass', $record);
            $doc = $searcharea->get_document($record);
            $this->assertInstanceOf('\core_search\document', $doc);

                        $dbreads = $DB->perf_get_reads();
            $doc = $searcharea->get_document($record);

                        $this->assertEquals($dbreads + 1, $DB->perf_get_reads());
            $this->assertInstanceOf('\core_search\document', $doc);
            $nrecords++;
        }
                $recordset->close();
        $this->assertEquals(2, $nrecords);

                $recordset = $searcharea->get_recordset_by_timestamp(time() + 2);

                $this->assertFalse($recordset->valid());
        $recordset->close();
    }

    
    public function test_entries_document() {
        global $DB;

        $searcharea = \core_search\manager::get_search_area($this->entryareaid);

        $user = self::getDataGenerator()->create_user();
        $course1 = self::getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user->id, $course1->id, 'teacher');

        $record = new stdClass();
        $record->course = $course1->id;

        $this->setUser($user);
        $glossary = self::getDataGenerator()->create_module('glossary', $record);
        $entry = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary);
        $entry->course = $glossary->course;

        $doc = $searcharea->get_document($entry);
        $this->assertInstanceOf('\core_search\document', $doc);
        $this->assertEquals($entry->id, $doc->get('itemid'));
        $this->assertEquals($course1->id, $doc->get('courseid'));
        $this->assertEquals($user->id, $doc->get('userid'));
        $this->assertEquals($entry->concept, $doc->get('title'));
        $this->assertEquals($entry->definition, $doc->get('content'));
    }

    
    public function test_entries_access() {
        global $DB;

                $searcharea = \core_search\manager::get_search_area($this->entryareaid);

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 'teacher');
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, 'student');

        $record = new stdClass();
        $record->course = $course1->id;

                $this->setUser($user1);
        $glossary1 = self::getDataGenerator()->create_module('glossary', $record);
        $teacherapproved = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary1);
        $teachernotapproved = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary1, array('approved' => false));

                $glossary2 = self::getDataGenerator()->create_module('glossary', $record);
        $this->setUser($user2);
        $studentapproved = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary2);
        $studentnotapproved = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary2, array('approved' => false));

                $this->setUser($user1);
        $glossary3 = self::getDataGenerator()->create_module('glossary', $record);
        $hidden = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary3);
        set_coursemodule_visible($glossary3->cmid, 0);

        $this->setUser($user2);
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($teacherapproved->id));
        $this->assertEquals(\core_search\manager::ACCESS_DENIED, $searcharea->check_access($teachernotapproved->id));
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($studentapproved->id));
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($studentnotapproved->id));
        $this->assertEquals(\core_search\manager::ACCESS_DENIED, $searcharea->check_access($hidden->id));
    }
}
