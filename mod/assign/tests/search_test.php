<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');


class mod_assign_search_testcase extends advanced_testcase {

    
    protected $assignareaid = null;

    public function setUp() {
        $this->resetAfterTest(true);
        set_config('enableglobalsearch', true);

        $this->assignareaid = \core_search\manager::generate_areaid('mod_assign', 'activity');

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

        $assign = $this->getDataGenerator()->create_module('assign', $record);
        $context = context_module::instance($assign->cmid);

                $filerecord = array(
            'contextid' => $context->id,
            'component' => 'mod_assign',
            'filearea'  => ASSIGN_INTROATTACHMENT_FILEAREA,
            'itemid'    => 0,
            'filepath'  => '/'
        );

                for ($i = 1; $i <= 4; $i++) {
            $filerecord['filename'] = 'myfile'.$i;
            $fs->create_file_from_string($filerecord, 'Test assign file '.$i);
        }

                $filerecord['filename'] = 'myfile5';
        $filerecord['filepath'] = '/subfolder/';
        $fs->create_file_from_string($filerecord, 'Test assign file 5');

                $searcharea = \core_search\manager::get_search_area($this->assignareaid);
        $this->assertInstanceOf('\mod_assign\search\activity', $searcharea);

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
