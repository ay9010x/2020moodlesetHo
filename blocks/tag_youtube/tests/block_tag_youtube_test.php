<?php



defined('MOODLE_INTERNAL') || die();


class core_block_tag_youtube_testcase extends advanced_testcase {

    
    public function test_after_install() {
        global $DB;

        $this->resetAfterTest(true);

                $this->assertTrue($DB->record_exists('block', array('name' => 'tag_youtube', 'visible' => 0)));
    }
}
