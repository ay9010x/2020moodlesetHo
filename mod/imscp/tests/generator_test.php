<?php




class mod_imscp_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB, $CFG, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('imscp', array('course' => $course->id)));
        $imscp = $this->getDataGenerator()->create_module('imscp', array('course' => $course));
        $records = $DB->get_records('imscp', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($imscp->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another imscp');
        $imscp = $this->getDataGenerator()->create_module('imscp', $params);
        $records = $DB->get_records('imscp', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another imscp', $records[$imscp->id]->name);

                        $params = array(
            'course' => $course->id,
            'packagepath' => $CFG->dirroot.'/mod/imscp/tests/packages/singlescobasic.zip'
        );
        $imscp = $this->getDataGenerator()->create_module('imscp', $params);

                $fs = get_file_storage();
        $params = array(
            'course' => $course->id,
            'package' => file_get_unused_draft_itemid()
        );
        $usercontext = context_user::instance($USER->id);
        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                'contextid' => $usercontext->id, 'itemid' => $params['package'],
                'filename' => 'singlescobasic.zip', 'filepath' => '/');
        $fs->create_file_from_pathname($filerecord, $CFG->dirroot.'/mod/imscp/tests/packages/singlescobasic.zip');
        $imscp = $this->getDataGenerator()->create_module('imscp', $params);
    }
}
