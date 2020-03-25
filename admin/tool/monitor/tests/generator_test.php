<?php



defined('MOODLE_INTERNAL') || die();


class tool_monitor_generator_testcase extends advanced_testcase {

    
    public function setUp() {
                set_config('enablemonitor', 1, 'tool_monitor');
    }

    
    public function test_create_rule() {
        $this->setAdminUser();
        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        $rulegenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

        $record = new stdClass();
        $record->courseid = $course->id;
        $record->userid = $user->id;

        $rule = $rulegenerator->create_rule($record);
        $this->assertInstanceOf('tool_monitor\rule', $rule);
        $this->assertEquals($rule->userid, $record->userid);
        $this->assertEquals($rule->courseid, $record->courseid);
    }

    
    public function test_create_subscription() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $rule = $monitorgenerator->create_rule();

        $record = new stdClass();
        $record->courseid = $course->id;
        $record->userid = $user->id;
        $record->ruleid = $rule->id;

        $subscription = $monitorgenerator->create_subscription($record);
        $this->assertEquals($record->courseid, $subscription->courseid);
        $this->assertEquals($record->ruleid, $subscription->ruleid);
        $this->assertEquals($record->userid, $subscription->userid);
        $this->assertEquals(0, $subscription->cmid);

                $this->setExpectedException('coding_exception');
        unset($record->ruleid);
        $monitorgenerator->create_subscription($record);
    }

    
    public function test_create_event_entries() {
        $this->setAdminUser();
        $this->resetAfterTest(true);
        $context = \context_system::instance();

                $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

                $eventdata = $monitorgenerator->create_event_entries();
        $this->assertEquals('\core\event\user_loggedin', $eventdata->eventname);
        $this->assertEquals($context->id, $eventdata->contextid);
        $this->assertEquals($context->contextlevel, $eventdata->contextlevel);
    }

    
    public function test_create_history() {
        $this->setAdminUser();
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $rule = $monitorgenerator->create_rule();

        $record = new \stdClass();
        $record->userid = $user->id;
        $record->ruleid = $rule->id;
        $sid = $monitorgenerator->create_subscription($record)->id;
        $record->sid = $sid;
        $historydata = $monitorgenerator->create_history($record);
        $this->assertEquals($record->userid, $historydata->userid);
        $this->assertEquals($record->sid, $historydata->sid);

                $record->userid = 1;
        $record->sid = 1;
        $historydata = $monitorgenerator->create_history($record);
        $this->assertEquals(1, $historydata->userid);
        $this->assertEquals(1, $historydata->sid);
    }
}