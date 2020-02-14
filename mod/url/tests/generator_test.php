<?php




class mod_url_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('url', array('course' => $course->id)));
        $url = $this->getDataGenerator()->create_module('url', array('course' => $course));
        $records = $DB->get_records('url', array('course' => $course->id), 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($url->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another url');
        $url = $this->getDataGenerator()->create_module('url', $params);
        $records = $DB->get_records('url', array('course' => $course->id), 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('Another url', $records[$url->id]->name);
    }
}
