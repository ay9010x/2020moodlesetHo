<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class tool_monitor_rule_manager_testcase extends advanced_testcase {

    
    public function setUp() {
                set_config('enablemonitor', 1, 'tool_monitor');
    }

    
    public function test_add_rule() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $now = time();

        $rule = new stdClass();
        $rule->userid = $user->id;
        $rule->courseid = $course->id;
        $rule->name = 'test rule 1';
        $rule->plugin = 'core';
        $rule->eventname = '\core\event\course_updated';
        $rule->description = 'test description 1';
        $rule->descriptionformat = FORMAT_HTML;
        $rule->frequency = 15;
        $rule->template = 'test template message';
        $rule->templateformat = FORMAT_HTML;
        $rule->timewindow = 300;
        $rule->timecreated = $now;
        $rule->timemodified = $now;

        $ruledata = \tool_monitor\rule_manager::add_rule($rule);
        foreach ($rule as $prop => $value) {
            $this->assertEquals($ruledata->$prop, $value);
        }
    }

    
    public function test_get_rule() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $rule = $monitorgenerator->create_rule();
        $rules1 = \tool_monitor\rule_manager::get_rule($rule->id);
        $this->assertInstanceOf('tool_monitor\rule', $rules1);
        $this->assertEquals($rules1, $rule);
    }

    
    public function test_update_rule() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $rule = $monitorgenerator->create_rule();

        $ruledata = new stdClass;
        $ruledata->id = $rule->id;
        $ruledata->frequency = 25;

        \tool_monitor\rule_manager::update_rule($ruledata);
        $this->assertEquals(25, $ruledata->frequency);

    }

    
    public function test_get_rules_by_courseid() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $record = new stdClass();
        $record->courseid = $course1->id;

        $record2 = new stdClass();
        $record2->courseid = $course2->id;

        $ruleids = array();
        for ($i = 0; $i < 10; $i++) {
            $rule = $monitorgenerator->create_rule($record);
            $ruleids[] = $rule->id;
            $rule = $monitorgenerator->create_rule();             $ruleids[] = $rule->id;
            $rule = $monitorgenerator->create_rule($record2);         }
        $ruledata = \tool_monitor\rule_manager::get_rules_by_courseid($course1->id);
        $this->assertEmpty(array_merge(array_diff(array_keys($ruledata), $ruleids), array_diff($ruleids, array_keys($ruledata))));
        $this->assertCount(20, $ruledata);
    }

    
    public function test_get_rules_by_plugin() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

        $record = new stdClass();
        $record->plugin = 'core';

        $record2 = new stdClass();
        $record2->plugin = 'mod_assign';

        $ruleids = array();
        for ($i = 0; $i < 10; $i++) {
            $rule = $monitorgenerator->create_rule($record);
            $ruleids[] = $rule->id;
            $rule = $monitorgenerator->create_rule($record2);         }

        $ruledata = \tool_monitor\rule_manager::get_rules_by_plugin('core');
        $this->assertEmpty(array_merge(array_diff(array_keys($ruledata), $ruleids), array_diff($ruleids, array_keys($ruledata))));
        $this->assertCount(10, $ruledata);
    }

    
    public function test_get_rules_by_event() {
        $this->setAdminUser();
        $this->resetAfterTest(true);

        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $rule = $monitorgenerator->create_rule();

        $record = new stdClass();
        $record->eventname = '\core\event\calendar_event_created';

        $record2 = new stdClass();
        $record2->eventname = '\core\event\calendar_event_updated';

        $ruleids = array();
        for ($i = 0; $i < 10; $i++) {
            $rule = $monitorgenerator->create_rule($record);
            $ruleids[] = $rule->id;
            $rule = $monitorgenerator->create_rule($record2);         }

        $ruledata = \tool_monitor\rule_manager::get_rules_by_event('\core\event\calendar_event_created');
        $this->assertEmpty(array_diff(array_keys($ruledata), $ruleids));
        $this->assertCount(10, $ruledata);
    }
}