<?php



defined('MOODLE_INTERNAL') || die();

class core_ajaxlib_testcase extends advanced_testcase {
    
    protected $oldlog;

    protected function setUp() {
        global $CFG;

        parent::setUp();
                $this->oldlog = ini_get('error_log');
        ini_set('error_log', "$CFG->dataroot/testlog.log");
    }

    protected function tearDown() {
        ini_set('error_log', $this->oldlog);
        parent::tearDown();
    }

    protected function helper_test_clean_output() {
        $this->resetAfterTest();

        $result = ajax_capture_output();

                $this->assertTrue($result);

        $result = ajax_check_captured_output();
        $this->assertEmpty($result);
    }

    protected function helper_test_dirty_output($expectexception = false) {
        $this->resetAfterTest();

                $content = "Some example content";

        $result = ajax_capture_output();

                $this->assertTrue($result);

                echo $content;

        if ($expectexception) {
            $this->setExpectedException('coding_exception');
            ajax_check_captured_output();
        } else {
            $result = ajax_check_captured_output();
            $this->assertEquals($result, $content);
        }
    }

    public function test_output_capture_normal_debug_none() {
                set_debugging(DEBUG_NONE);
        $this->helper_test_clean_output();
    }

    public function test_output_capture_normal_debug_normal() {
                set_debugging(DEBUG_NORMAL);
        $this->helper_test_clean_output();
    }

    public function test_output_capture_normal_debug_all() {
                set_debugging(DEBUG_ALL);
        $this->helper_test_clean_output();
    }

    public function test_output_capture_normal_debugdeveloper() {
                set_debugging(DEBUG_DEVELOPER);
        $this->helper_test_clean_output();
    }

    public function test_output_capture_error_debug_none() {
                set_debugging(DEBUG_NONE);
        $this->helper_test_dirty_output();
    }

    public function test_output_capture_error_debug_normal() {
                set_debugging(DEBUG_NORMAL);
        $this->helper_test_dirty_output();
    }

    public function test_output_capture_error_debug_all() {
                set_debugging(DEBUG_ALL);
        $this->helper_test_dirty_output();
    }

    public function test_output_capture_error_debugdeveloper() {
                set_debugging(DEBUG_DEVELOPER);
        $this->helper_test_dirty_output(true);
    }

}
