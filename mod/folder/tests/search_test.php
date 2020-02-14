<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');


class mod_folder_search_testcase extends advanced_testcase {

    
    protected $folderareaid = null;

    public function setUp() {
        $this->resetAfterTest(true);
        set_config('enableglobalsearch', true);

        $this->folderareaid = \core_search\manager::generate_areaid('mod_folder', 'activity');

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
            'filepath'  => '/'
        );

                for ($i = 1; $i <= 4; $i++) {
            $filerecord['filename'] = 'myfile'.$i;
            $fs->create_file_from_string($filerecord, 'Test folder file '.$i);
        }

                $filerecord['filename'] = 'myfile5';
        $filerecord['filepath'] = '/subfolder/';
        $fs->create_file_from_string($filerecord, 'Test folder file 5');

        $this->getDataGenerator()->create_module('folder', $record);

                $searcharea = \core_search\manager::get_search_area($this->folderareaid);
        $this->assertInstanceOf('\mod_folder\search\activity', $searcharea);

        $recordset = $searcharea->get_recordset_by_timestamp(0);
        $nrecords = 0;
        foreach ($recordset as $record) {
            $doc = $searcharea->get_document($record);
            $searcharea->attach_files($doc);
            $files = $doc->get_files();

                        $this->assertCount(5, $files);

                        $filenames = array();
            foreach ($files as $file) {
                $filenames[] = $file->get_filename();
            }
            sort($filenames);

            for ($i = 1; $i <= 5; $i++) {
                $this->assertEquals('myfile'.$i, $filenames[($i - 1)]);
            }

            $nrecords++;
        }

                $recordset->close();
        $this->assertEquals(1, $nrecords);
    }

}
