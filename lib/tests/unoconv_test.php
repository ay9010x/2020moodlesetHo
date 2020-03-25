<?php



defined('MOODLE_INTERNAL') || die();



class core_unoconv_testcase extends advanced_testcase {

    public function get_converted_document_provider() {
        $fixturepath = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        return [
            'HTML => PDF' => [
                'source'            => $fixturepath . 'unoconv-source.html',
                'sourcefilename'    => 'test.html',
                'format'            => 'pdf',
                'mimetype'          => 'application/pdf',
            ],
            'docx => PDF' => [
                'source'            => $fixturepath . 'unoconv-source.docx',
                'sourcefilename'    => 'test.docx',
                'format'            => 'pdf',
                'mimetype'          => 'application/pdf',
            ],
            'HTML => TXT' => [
                'source'            => $fixturepath . 'unoconv-source.html',
                'sourcefilename'    => 'test.html',
                'format'            => 'txt',
                'mimetype'          => 'text/plain',
            ],
            'docx => TXT' => [
                'source'            => $fixturepath . 'unoconv-source.docx',
                'sourcefilename'    => 'test.docx',
                'format'            => 'txt',
                'mimetype'          => 'text/plain',
            ],
        ];
    }

    
    public function test_get_converted_document($source, $sourcefilename, $format, $mimetype) {
        global $CFG;

        if (empty($CFG->pathtounoconv) || !file_is_executable(trim($CFG->pathtounoconv))) {
                        return $this->markTestSkipped();
        }

        $this->resetAfterTest();

        $filerecord = array(
            'contextid' => context_system::instance()->id,
            'component' => 'test',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => $sourcefilename,
        );

        $fs = get_file_storage();
                $testfile = $fs->create_file_from_pathname($filerecord, $source);

        $result = $fs->get_converted_document($testfile, $format);
        $this->assertNotFalse($result);
        $this->assertSame($mimetype, $result->get_mimetype());
        $this->assertGreaterThan(0, $result->get_filesize());

                $new = $fs->get_converted_document($testfile, $format, true);
        $this->assertNotFalse($new);
        $this->assertSame($mimetype, $new->get_mimetype());
        $this->assertGreaterThan(0, $new->get_filesize());
        $this->assertNotEquals($result->get_id(), $new->get_id());
                    }
}
