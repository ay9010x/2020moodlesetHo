<?php



defined('MOODLE_INTERNAL') || die();


class testable_core_update_validator extends \core\update\validator {

    public function testable_parse_version_php($fullpath) {
        return parent::parse_version_php($fullpath);
    }

    public function get_plugintype_location($plugintype) {

        $testableroot = make_temp_directory('testable_core_update_validator/plugintypes');
        if (!file_exists($testableroot.'/'.$plugintype)) {
            make_temp_directory('testable_core_update_validator/plugintypes/'.$plugintype);
        }

        return $testableroot.'/'.$plugintype;
    }
}
