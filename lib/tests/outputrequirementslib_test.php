<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/outputrequirementslib.php');


class core_outputrequirementslib_testcase extends advanced_testcase {
    public function test_string_for_js() {
        $this->resetAfterTest();

        $page = new moodle_page();
        $page->requires->string_for_js('course', 'moodle', 1);
        $page->requires->string_for_js('course', 'moodle', 1);
        $this->setExpectedException('coding_exception');
        $page->requires->string_for_js('course', 'moodle', 2);

                    }

    public function test_one_time_output_normal_case() {
        $page = new moodle_page();
        $this->assertTrue($page->requires->should_create_one_time_item_now('test_item'));
        $this->assertFalse($page->requires->should_create_one_time_item_now('test_item'));
    }

    public function test_one_time_output_repeat_output_throws() {
        $page = new moodle_page();
        $page->requires->set_one_time_item_created('test_item');
        $this->setExpectedException('coding_exception');
        $page->requires->set_one_time_item_created('test_item');
    }

    public function test_one_time_output_different_pages_independent() {
        $firstpage = new moodle_page();
        $secondpage = new moodle_page();
        $this->assertTrue($firstpage->requires->should_create_one_time_item_now('test_item'));
        $this->assertTrue($secondpage->requires->should_create_one_time_item_now('test_item'));
    }

    
    public function test_jquery_plugin() {
        global $CFG;

        $this->resetAfterTest();

                $CFG->slasharguments = 1;

        $page = new moodle_page();
        $requirements = $page->requires;
                $this->assertTrue($requirements->jquery_plugin('jquery'));
        $this->assertTrue($requirements->jquery_plugin('ui'));

                $requirecode = $requirements->get_top_of_body_code();
                $this->assertFalse(strpos($requirecode, '\\'), "Output contains backslashes: " . $requirecode);

                $CFG->slasharguments = 0;

        $page = new moodle_page();
        $requirements = $page->requires;
                $this->assertTrue($requirements->jquery_plugin('jquery'));
        $this->assertTrue($requirements->jquery_plugin('ui'));

                $requirecode = $requirements->get_top_of_body_code();
                $this->assertFalse(strpos($requirecode, '\\'), "Output contains backslashes: " . $requirecode);
    }
}
