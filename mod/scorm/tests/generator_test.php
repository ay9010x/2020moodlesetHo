<?php




class mod_scorm_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB, $CFG, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('scorm', array('course' => $course->id)));
        $scorm = $this->getDataGenerator()->create_module('scorm', array('course' => $course));
        $records = $DB->get_records('scorm', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($scorm->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another scorm');
        $scorm = $this->getDataGenerator()->create_module('scorm', $params);
        $records = $DB->get_records('scorm', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another scorm', $records[$scorm->id]->name);

                        $params = array(
            'course' => $course->id,
            'packagefilepath' => $CFG->dirroot.'/mod/scorm/tests/packages/singlescobasic.zip'
        );
        $scorm = $this->getDataGenerator()->create_module('scorm', $params);

                $fs = get_file_storage();
        $params = array(
            'course' => $course->id,
            'packagefile' => file_get_unused_draft_itemid()
        );
        $usercontext = context_user::instance($USER->id);
        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                'contextid' => $usercontext->id, 'itemid' => $params['packagefile'],
                'filename' => 'singlescobasic.zip', 'filepath' => '/');
        $fs->create_file_from_pathname($filerecord, $CFG->dirroot.'/mod/scorm/tests/packages/singlescobasic.zip');
        $scorm = $this->getDataGenerator()->create_module('scorm', $params);
    }
}
