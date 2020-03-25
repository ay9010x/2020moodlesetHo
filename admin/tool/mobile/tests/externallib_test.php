<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use tool_mobile\external;


class tool_mobile_external_testcase extends externallib_advanced_testcase {

    
    public function test_get_plugins_supporting_mobile() {
        $result = external::get_plugins_supporting_mobile();
        $result = external_api::clean_returnvalue(external::get_plugins_supporting_mobile_returns(), $result);
        $this->assertCount(0, $result['warnings']);
        $this->assertArrayHasKey('plugins', $result);
        $this->assertTrue(is_array($result['plugins']));
    }

}
