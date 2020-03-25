<?php



defined('MOODLE_INTERNAL') || die();



class mod_resource_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB, $SITE;

        $this->resetAfterTest(true);

                $this->setAdminUser();

                $this->assertEquals(0, $DB->count_records('resource'));

                $generator = $this->getDataGenerator()->get_plugin_generator('mod_resource');
        $this->assertInstanceOf('mod_resource_generator', $generator);
        $this->assertEquals('resource', $generator->get_modulename());

                $generator->create_instance(array('course' => $SITE->id));
        $generator->create_instance(array('course' => $SITE->id));
        $resource = $generator->create_instance(array('course' => $SITE->id));
        $this->assertEquals(3, $DB->count_records('resource'));

                $cm = get_coursemodule_from_instance('resource', $resource->id);
        $this->assertEquals($resource->id, $cm->instance);
        $this->assertEquals('resource', $cm->modname);
        $this->assertEquals($SITE->id, $cm->course);

                $context = context_module::instance($cm->id);
        $this->assertEquals($resource->cmid, $context->instanceid);

                $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_resource', 'content', false, '', false);
        $this->assertEquals(1, count($files));
    }
}
