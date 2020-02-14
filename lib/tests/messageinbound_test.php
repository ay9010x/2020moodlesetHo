<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class core_messageinbound_testcase extends advanced_testcase {

    
    public function test_messageinbound_handler_trim($file, $source, $expectedplain, $expectedhtml) {
        $this->resetAfterTest();

        $mime = Horde_Mime_Part::parseMessage($source);
        if ($plainpartid = $mime->findBody('plain')) {
            $messagedata = new stdClass();
            $messagedata->plain = $mime->getPart($plainpartid)->getContents();
            $messagedata->html = '';

            list($message, $format) = test_handler::remove_quoted_text($messagedata);
            list ($message, $expectedplain) = preg_replace("#\r\n#", "\n", array($message, $expectedplain));

                        $this->assertEquals($expectedplain, $message);
            $this->assertEquals(FORMAT_PLAIN, $format);
        }

        if ($htmlpartid = $mime->findBody('html')) {
            $messagedata = new stdClass();
            $messagedata->plain = '';
            $messagedata->html = $mime->getPart($htmlpartid)->getContents();

            list($message, $format) = test_handler::remove_quoted_text($messagedata);

                        list ($message, $expectedhtml) = preg_replace("#\r\n#", "\n", array($message, $expectedhtml));
            $this->assertEquals($expectedhtml, $message);
            $this->assertEquals(FORMAT_PLAIN, $format);
        }
    }

    public function message_inbound_handler_trim_testprovider() {
        $fixturesdir = realpath(__DIR__ . '/fixtures/messageinbound/');
        $tests = array();
        $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fixturesdir),
                \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($iterator as $file) {
            if (!preg_match('/\.test$/', $file)) {
                continue;
            }

            try {
                $testdata = $this->read_test_file($file, $fixturesdir);
            } catch (\Exception $e) {
                die($e->getMessage());
            }

            $test = array(
                                        basename($file),

                    $testdata['FULLSOURCE'],

                                        $testdata['EXPECTEDPLAIN'],

                                        $testdata['EXPECTEDHTML'],
                );

            $tests[basename($file)] = $test;
        }
        return $tests;
    }

    protected function read_test_file(\SplFileInfo $file, $fixturesdir) {
                $content = file_get_contents($file->getRealPath());
        $content = preg_replace("#\r\n#", "\n", $content);
        $tokens = preg_split('#(?:^|\n*)----([A-Z]+)----\n#', file_get_contents($file->getRealPath()),
                null, PREG_SPLIT_DELIM_CAPTURE);
        $sections = array(
                        'FULLSOURCE'        => true,
            'EXPECTEDPLAIN'     => true,
            'EXPECTEDHTML'      => true,
            'CLIENT'            => true,         );
        $section = null;
        $data = array();
        foreach ($tokens as $i => $token) {
            if (null === $section && empty($token)) {
                continue;             }
            if (null === $section) {
                if (!isset($sections[$token])) {
                    throw new coding_exception(sprintf(
                        'The test file "%s" should not contain a section named "%s".',
                        basename($file),
                        $token
                    ));
                }
                $section = $token;
                continue;
            }
            $sectiondata = $token;
            $data[$section] = $sectiondata;
            $section = $sectiondata = null;
        }
        foreach ($sections as $section => $required) {
            if ($required && !isset($data[$section])) {
                throw new coding_exception(sprintf(
                    'The test file "%s" must have a section named "%s".',
                    str_replace($fixturesdir.'/', '', $file),
                    $section
                ));
            }
        }
        return $data;
    }
}


class test_handler extends \core\message\inbound\handler {

    public static function remove_quoted_text($messagedata) {
        return parent::remove_quoted_text($messagedata);
    }

    public function get_name() {}

    public function get_description() {}

    public function process_message(stdClass $record, stdClass $messagedata) {}
}
