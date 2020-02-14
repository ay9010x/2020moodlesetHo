<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');


class mod_resource_search_testcase extends advanced_testcase {

    
    protected $resourceareaid = null;

    public function setUp() {
        $this->resetAfterTest(true);
        set_config('enableglobalsearch', true);

        $this->resourceareaid = \core_search\manager::generate_areaid('mod_resource', 'activity');

                $search = testable_core_search::instance();
    }

    
    public function test_attach_files() {
        global $USER;

        $this->setAdminUser();
                $course = $this->getDataGenerator()->create_course();

        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);

        $record = new stdClass();
        $record->course = $course->id;
        $record->files = file_get_unused_draft_itemid();

                $filerecord = array(
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $record->files,
            'filepath'  => '/',
            'filename'  => 'mainfile',
            'sortorder' => 1
        );
        $fs->create_file_from_string($filerecord, 'Test resource file');

                $filerecord['filename'] = 'extrafile';
        $filerecord['sortorder'] = 0;
        $fs->create_file_from_string($filerecord, 'Test resource file 2');

        $resource = $this->getDataGenerator()->create_module('resource', $record);

        $searcharea = \core_search\manager::get_search_area($this->resourceareaid);
        $this->assertInstanceOf('\mod_resource\search\activity', $searcharea);

        $recordset = $searcharea->get_recordset_by_timestamp(0);
        $nrecords = 0;
        foreach ($recordset as $record) {
            $doc = $searcharea->get_document($record);
            $searcharea->attach_files($doc);
            $files = $doc->get_files();

                        $this->assertCount(1, $files);
            $file = reset($files);
            $this->assertEquals('mainfile', $file->get_filename());

            $nrecords++;
        }

        $recordset->close();
        $this->assertEquals(1, $nrecords);
    }

}
