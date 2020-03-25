<?php



defined('MOODLE_INTERNAL') || die();


class core_event_deprecated_testcase extends advanced_testcase {

    
    public function test_deprecated_course_module_instances_list_viewed_events() {

                require_once(__DIR__.'/fixtures/event_mod_badfixtures.php');
        $this->assertDebuggingCalled(null, DEBUG_DEVELOPER);

    }
}
