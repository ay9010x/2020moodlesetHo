<?php




class mod_chat_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('chat', array('course' => $course->id)));
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('chat', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('chat', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('chat', array('id' => $chat->id)));

        $params = array('course' => $course->id, 'name' => 'One more chat');
        $chat = $this->getDataGenerator()->create_module('chat', $params);
        $this->assertEquals(2, $DB->count_records('chat', array('course' => $course->id)));
        $this->assertEquals('One more chat', $DB->get_field_select('chat', 'name', 'id = :id', array('id' => $chat->id)));
    }

}
