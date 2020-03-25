<?php




class mod_folder_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('folder', array('course' => $course->id)));
        $folder = $this->getDataGenerator()->create_module('folder', array('course' => $course));
        $records = $DB->get_records('folder', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($folder->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another folder');
        $folder = $this->getDataGenerator()->create_module('folder', $params);
        $records = $DB->get_records('folder', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another folder', $records[$folder->id]->name);

                $params = array(
            'course' => $course->id,
            'files' => file_get_unused_draft_itemid()
        );
        $usercontext = context_user::instance($USER->id);
        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                'contextid' => $usercontext->id, 'itemid' => $params['files'],
                'filename' => 'file1.txt', 'filepath' => '/');
        $fs = get_file_storage();
        $fs->create_file_from_string($filerecord, 'Test file contents');
        $folder = $this->getDataGenerator()->create_module('folder', $params);
    }
}
