<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');


class mod_wiki_search_testcase extends advanced_testcase {

    
    protected $wikicollabpageareaid = null;

    public function setUp() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        set_config('enableglobalsearch', true);

        $this->wikicollabpageareaid = \core_search\manager::generate_areaid('mod_wiki', 'collaborative_page');

                $search = testable_core_search::instance();
    }

    
    public function test_search_enabled() {
        $searcharea = \core_search\manager::get_search_area($this->wikicollabpageareaid);
        list($componentname, $varname) = $searcharea->get_config_var_name();

                $this->assertTrue($searcharea->is_enabled());

        set_config($varname . '_enabled', false, $componentname);
        $this->assertFalse($searcharea->is_enabled());

        set_config($varname . '_enabled', true, $componentname);
        $this->assertTrue($searcharea->is_enabled());
    }

    
    public function test_collaborative_page_indexing() {
        global $DB;

                $searcharea = \core_search\manager::get_search_area($this->wikicollabpageareaid);
        $this->assertInstanceOf('\mod_wiki\search\collaborative_page', $searcharea);

        $wikigenerator = $this->getDataGenerator()->get_plugin_generator('mod_wiki');
        $course1 = self::getDataGenerator()->create_course();

        $collabwiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course1->id));
        $cpage1 = $wikigenerator->create_first_page($collabwiki);
        $cpage2 = $wikigenerator->create_content($collabwiki);
        $cpage3 = $wikigenerator->create_content($collabwiki);

        $indwiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course1->id, 'wikimode' => 'individual'));
        $ipage1 = $wikigenerator->create_first_page($indwiki);
        $ipage2 = $wikigenerator->create_content($indwiki);
        $ipage3 = $wikigenerator->create_content($indwiki);

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

                $this->assertEquals(3, $nrecords);

                $recordset = $searcharea->get_recordset_by_timestamp(time() + 2);

                $this->assertFalse($recordset->valid());
        $recordset->close();
    }

    
    public function test_collaborative_page_check_access() {
        global $DB;

                $searcharea = \core_search\manager::get_search_area($this->wikicollabpageareaid);
        $this->assertInstanceOf('\mod_wiki\search\collaborative_page', $searcharea);

        $user1 = self::getDataGenerator()->create_user();
        $course1 = self::getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');

        $wikigenerator = $this->getDataGenerator()->get_plugin_generator('mod_wiki');

        $collabwiki = $this->getDataGenerator()->create_module('wiki', array('course' => $course1->id));
        $cpage1 = $wikigenerator->create_first_page($collabwiki);

        $this->setAdminUser();
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($cpage1->id));

        $this->setUser($user1);
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $searcharea->check_access($cpage1->id));

        $this->assertEquals(\core_search\manager::ACCESS_DELETED, $searcharea->check_access($cpage1->id + 10));
    }
}
