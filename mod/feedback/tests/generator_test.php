<?php



global $CFG;


class mod_feedback_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('feedback', array('course' => $course->id)));
        $feedback = $this->getDataGenerator()->create_module('feedback', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('feedback', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('feedback', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('feedback', array('id' => $feedback->id)));

        $params = array('course' => $course->id, 'name' => 'One more feedback');
        $feedback = $this->getDataGenerator()->create_module('feedback', $params);
        $this->assertEquals(2, $DB->count_records('feedback', array('course' => $course->id)));
        $this->assertEquals('One more feedback', $DB->get_field_select('feedback', 'name', 'id = :id',
                array('id' => $feedback->id)));
    }

}

