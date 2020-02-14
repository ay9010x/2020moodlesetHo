<?php



defined('MOODLE_INTERNAL') || die();



class mod_label_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('label'));

        $course = $this->getDataGenerator()->create_course();

        
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_label');
        $this->assertInstanceOf('mod_label_generator', $generator);
        $this->assertEquals('label', $generator->get_modulename());

        $generator->create_instance(array('course'=>$course->id));
        $generator->create_instance(array('course'=>$course->id));
        $label = $generator->create_instance(array('course'=>$course->id));
        $this->assertEquals(3, $DB->count_records('label'));

        $cm = get_coursemodule_from_instance('label', $label->id);
        $this->assertEquals($label->id, $cm->instance);
        $this->assertEquals('label', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($label->cmid, $context->instanceid);
    }
}
