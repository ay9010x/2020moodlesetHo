<?php



defined('MOODLE_INTERNAL') || die();



class mod_page_generator_testcase extends advanced_testcase {
    public function test_generator() {
        global $DB, $SITE;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('page'));

        
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_page');
        $this->assertInstanceOf('mod_page_generator', $generator);
        $this->assertEquals('page', $generator->get_modulename());

        $generator->create_instance(array('course'=>$SITE->id));
        $generator->create_instance(array('course'=>$SITE->id));
        $page = $generator->create_instance(array('course'=>$SITE->id));
        $this->assertEquals(3, $DB->count_records('page'));

        $cm = get_coursemodule_from_instance('page', $page->id);
        $this->assertEquals($page->id, $cm->instance);
        $this->assertEquals('page', $cm->modname);
        $this->assertEquals($SITE->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($page->cmid, $context->instanceid);
    }
}
