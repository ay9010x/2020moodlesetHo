<?php



defined('MOODLE_INTERNAL') || die();


class mod_lti_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('lti'));

        $course = $this->getDataGenerator()->create_course();

        
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_lti');
        $this->assertInstanceOf('mod_lti_generator', $generator);
        $this->assertEquals('lti', $generator->get_modulename());

        $generator->create_instance(array('course' => $course->id));
        $generator->create_instance(array('course' => $course->id));
        $lti = $generator->create_instance(array('course' => $course->id));
        $this->assertEquals(3, $DB->count_records('lti'));

        $cm = get_coursemodule_from_instance('lti', $lti->id);
        $this->assertEquals($lti->id, $cm->instance);
        $this->assertEquals('lti', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($lti->cmid, $context->instanceid);

                $lti = $generator->create_instance(array('course' => $course->id, 'assessed' => 1, 'scale' => 100));
        $gitem = $DB->get_record('grade_items', array('courseid' => $course->id, 'itemtype' => 'mod',
            'itemmodule' => 'lti', 'iteminstance' => $lti->id));
        $this->assertNotEmpty($gitem);
        $this->assertEquals(100, $gitem->grademax);
        $this->assertEquals(0, $gitem->grademin);
        $this->assertEquals(GRADE_TYPE_VALUE, $gitem->gradetype);
    }
}
