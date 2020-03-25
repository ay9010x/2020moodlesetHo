<?php



defined('MOODLE_INTERNAL') || die();

class antivirus_clamav_scanner_testcase extends advanced_testcase {
    protected $tempfile;

    protected function setUp() {
        $this->resetAfterTest();

                $tempfolder = make_request_directory(false);
        $this->tempfile = $tempfolder . '/' . rand();
        touch($this->tempfile);
    }

    protected function tearDown() {
        @unlink($this->tempfile);
    }

    public function test_scan_file_not_exists() {
        $antivirus = $this->getMockBuilder('\antivirus_clamav\scanner')
                ->setMethods(array('scan_file_execute_commandline', 'message_admins'))
                ->getMock();

                $nonexistingfile = $this->tempfile . '_';
        $this->assertFileNotExists($nonexistingfile);
                $antivirus->scan_file($nonexistingfile, '', true);
        $this->assertDebuggingCalled();
    }

    public function test_scan_file_no_virus() {
        $antivirus = $this->getMockBuilder('\antivirus_clamav\scanner')
                ->setMethods(array('scan_file_execute_commandline', 'message_admins'))
                ->getMock();

                        $antivirus->method('scan_file_execute_commandline')->willReturn(array(0, ''));

                $antivirus->expects($this->never())->method('message_admins');

                $this->assertFileExists($this->tempfile);
        try {
            $antivirus->scan_file($this->tempfile, '', true);
        } catch (\core\antivirus\scanner_exception $e) {
            $this->fail('Exception scanner_exception is not expected in clean file scanning.');
        }
                $this->assertFileExists($this->tempfile);
    }

    public function test_scan_file_virus() {
        $antivirus = $this->getMockBuilder('\antivirus_clamav\scanner')
                ->setMethods(array('scan_file_execute_commandline', 'message_admins'))
                ->getMock();

                        $antivirus->method('scan_file_execute_commandline')->willReturn(array(1, ''));

                $antivirus->expects($this->never())->method('message_admins');

                $this->assertFileExists($this->tempfile);
        try {
            $antivirus->scan_file($this->tempfile, '', false);
        } catch (\moodle_exception $e) {
            $this->assertInstanceOf('\core\antivirus\scanner_exception', $e);
        }
                $this->assertFileExists($this->tempfile);

                try {
            $antivirus->scan_file($this->tempfile, '', true);
        } catch (\moodle_exception $e) {
            $this->assertInstanceOf('\core\antivirus\scanner_exception', $e);
        }
                $this->assertFileNotExists($this->tempfile);
    }

    public function test_scan_file_error_donothing() {
        $antivirus = $this->getMockBuilder('\antivirus_clamav\scanner')
                ->setMethods(array('scan_file_execute_commandline', 'message_admins', 'get_config'))
                ->getMock();

                        $antivirus->method('scan_file_execute_commandline')->willReturn(array(2, 'someerror'));

                $antivirus->expects($this->atLeastOnce())->method('message_admins')->with($this->equalTo('someerror'));

                $configmap = array(array('clamfailureonupload', 'donothing'));
        $antivirus->method('get_config')->will($this->returnValueMap($configmap));

                $this->assertFileExists($this->tempfile);
        try {
            $antivirus->scan_file($this->tempfile, '', true);
        } catch (\core\antivirus\scanner_exception $e) {
            $this->fail('Exception scanner_exception is not expected with config setting to do nothing on error.');
        }
                $this->assertFileExists($this->tempfile);
    }

    public function test_scan_file_error_actlikevirus() {
        $antivirus = $this->getMockBuilder('\antivirus_clamav\scanner')
                ->setMethods(array('scan_file_execute_commandline', 'message_admins', 'get_config'))
                ->getMock();

                        $antivirus->method('scan_file_execute_commandline')->willReturn(array(2, 'someerror'));

                $antivirus->expects($this->atLeastOnce())->method('message_admins')->with($this->equalTo('someerror'));

                $configmap = array(array('clamfailureonupload', 'actlikevirus'));
        $antivirus->method('get_config')->will($this->returnValueMap($configmap));

                $this->assertFileExists($this->tempfile);
        try {
            $antivirus->scan_file($this->tempfile, '', false);
        } catch (\moodle_exception $e) {
            $this->assertInstanceOf('\core\antivirus\scanner_exception', $e);
        }
                $this->assertFileExists($this->tempfile);

                try {
            $antivirus->scan_file($this->tempfile, '', true);
        } catch (\moodle_exception $e) {
            $this->assertInstanceOf('\core\antivirus\scanner_exception', $e);
        }
                $this->assertFileNotExists($this->tempfile);
    }
}
