<?php



defined('MOODLE_INTERNAL') || die();

use tool_filetypes\utils;


class tool_filetypes_test extends advanced_testcase {
    
    public function test_is_extension_invalid() {
                $this->assertTrue(utils::is_extension_invalid('pdf'));

                $this->assertFalse(utils::is_extension_invalid('frog'));

                $this->assertFalse(utils::is_extension_invalid('pdf', 'pdf'));

                $this->assertTrue(utils::is_extension_invalid(''));

                $this->assertTrue(utils::is_extension_invalid('.frog'));
    }

    
    public function test_is_defaulticon_allowed() {
                        $this->assertTrue(utils::is_defaulticon_allowed('application/x-frog'));

                        $this->assertFalse(utils::is_defaulticon_allowed('text/plain'));

                        $this->assertTrue(utils::is_defaulticon_allowed('text/plain', 'txt'));
    }

    
    public function test_get_icons_from_path() {
                $icons = utils::get_icons_from_path(__DIR__ . '/fixtures');

                                $this->assertEquals(array('frog' => 'frog', 'zombie' => 'zombie'), $icons);
    }
}
