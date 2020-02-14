<?php



use mod_lti\service_exception_handler;

defined('MOODLE_INTERNAL') || die();


class mod_lti_service_exception_handler_testcase extends advanced_testcase {
    
    public function test_handle() {
        $handler = new service_exception_handler(false);
        $handler->set_message_id('123');
        $handler->set_message_type('testRequest');
        $handler->handle(new Exception('Error happened'));

        $this->expectOutputRegex('/imsx_codeMajor>failure/');
        $this->expectOutputRegex('/imsx_description>Error happened/');
        $this->expectOutputRegex('/imsx_messageRefIdentifier>123/');
        $this->expectOutputRegex('/imsx_operationRefIdentifier>testRequest/');
        $this->expectOutputRegex('/imsx_POXBody><testResponse/');
    }

    
    public function test_handle_early_error() {
        $handler = new service_exception_handler(false);
        $handler->handle(new Exception('Error happened'));

        $this->expectOutputRegex('/imsx_codeMajor>failure/');
        $this->expectOutputRegex('/imsx_description>Error happened/');
        $this->expectOutputRegex('/imsx_messageRefIdentifier\/>/');
        $this->expectOutputRegex('/imsx_operationRefIdentifier>unknownRequest/');
        $this->expectOutputRegex('/imsx_POXBody><unknownResponse/');
    }

    
    public function test_handle_log() {
        global $CFG;

        $this->resetAfterTest();

        $handler = new service_exception_handler(true);

        ob_start();
        $handler->handle(new Exception('Error happened'));
        ob_end_clean();

        $this->assertTrue(is_dir($CFG->dataroot.'/temp/mod_lti'));
        $files = glob($CFG->dataroot.'/temp/mod_lti/mod*');
        $this->assertEquals(1, count($files));
    }
}