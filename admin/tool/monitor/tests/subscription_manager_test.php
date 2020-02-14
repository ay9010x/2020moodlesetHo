<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class tool_monitor_subscription_manager_testcase extends advanced_testcase {

    
    public function test_count_rule_subscriptions() {

        $this->setAdminUser();
        $this->resetAfterTest(true);

                $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

                $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $rule1 = $monitorgenerator->create_rule();
        $rule2 = $monitorgenerator->create_rule();
        $subs = \tool_monitor\subscription_manager::count_rule_subscriptions($rule1->id);

                $this->assertEquals(0, $subs);

                $record = new stdClass;
        $record->ruleid = $rule1->id;
        $record->userid = $user1->id;
        $monitorgenerator->create_subscription($record);

                $record->userid = $user2->id;
        $monitorgenerator->create_subscription($record);

                $record->ruleid = $rule2->id;
        $monitorgenerator->create_subscription($record);

                $subs1 = \tool_monitor\subscription_manager::count_rule_subscriptions($rule1->id);
        $subs2 = \tool_monitor\subscription_manager::count_rule_subscriptions($rule2->id);
        $this->assertEquals(2, $subs1);
        $this->assertEquals(1, $subs2);
    }
}