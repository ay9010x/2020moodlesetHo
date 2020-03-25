<?php



defined('MOODLE_INTERNAL') || die();



class mod_data_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('data'));

        $course = $this->getDataGenerator()->create_course();

        
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_data');
        $this->assertInstanceOf('mod_data_generator', $generator);
        $this->assertEquals('data', $generator->get_modulename());

        $generator->create_instance(array('course'=>$course->id));
        $generator->create_instance(array('course'=>$course->id));
        $data = $generator->create_instance(array('course'=>$course->id));
        $this->assertEquals(3, $DB->count_records('data'));

        $cm = get_coursemodule_from_instance('data', $data->id);
        $this->assertEquals($data->id, $cm->instance);
        $this->assertEquals('data', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($data->cmid, $context->instanceid);

                $data = $generator->create_instance(array('course'=>$course->id, 'assessed'=>1, 'scale'=>100));
        $gitem = $DB->get_record('grade_items', array('courseid'=>$course->id, 'itemtype'=>'mod', 'itemmodule'=>'data', 'iteminstance'=>$data->id));
        $this->assertNotEmpty($gitem);
        $this->assertEquals(100, $gitem->grademax);
        $this->assertEquals(0, $gitem->grademin);
        $this->assertEquals(GRADE_TYPE_VALUE, $gitem->gradetype);

    }
}
