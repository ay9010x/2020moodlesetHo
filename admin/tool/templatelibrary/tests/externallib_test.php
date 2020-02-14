<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

use tool_templatelibrary\external;


class tool_templatelibrary_external_testcase extends externallib_advanced_testcase {

    
    public function test_list_templates() {
        $result = external::list_templates('', '');
        $count = count($result);
                $this->assertGreaterThan(3, $count);
    }

    
    public function test_list_templates_for_component() {
        $result = external::list_templates('tool_templatelibrary', '');
        $count = count($result);
        $this->assertEquals(3, $count);

        $this->assertContains("tool_templatelibrary/display_template", $result);
        $this->assertContains("tool_templatelibrary/search_results", $result);
        $this->assertContains("tool_templatelibrary/list_templates_page", $result);
    }

    
    public function test_list_templates_with_filter() {
        $result = external::list_templates('tool_templatelibrary', 'page');
        $count = count($result);
                $this->assertEquals(1, $count);
        $this->assertEquals($result[0], "tool_templatelibrary/list_templates_page");
    }

    public function test_load_canonical_template() {
        global $CFG;

        $originaltheme = $CFG->theme;
                $CFG->theme = 'base';

        $template = external::load_canonical_template('core', 'notification_error');

                $this->assertContains('@template core/notification_error', $template);

                $CFG->theme = $originaltheme;
    }
}
