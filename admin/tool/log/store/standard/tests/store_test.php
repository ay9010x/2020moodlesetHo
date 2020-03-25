<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/event.php');
require_once(__DIR__ . '/fixtures/restore_hack.php');

class logstore_standard_store_testcase extends advanced_testcase {
    public function test_log_writing() {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback(); 
        $this->setAdminUser();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $module1 = $this->getDataGenerator()->create_module('resource', array('course' => $course1));
        $course2 = $this->getDataGenerator()->create_course();
        $module2 = $this->getDataGenerator()->create_module('resource', array('course' => $course2));

                set_config('enabled_stores', '', 'tool_log');
        $manager = get_log_manager(true);
        $stores = $manager->get_readers();
        $this->assertCount(0, $stores);

                set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');
        set_config('logguests', 1, 'logstore_standard');
        $manager = get_log_manager(true);

        $stores = $manager->get_readers();
        $this->assertCount(1, $stores);
        $this->assertEquals(array('logstore_standard'), array_keys($stores));
        
        $store = $stores['logstore_standard'];
        $this->assertInstanceOf('logstore_standard\log\store', $store);
        $this->assertInstanceOf('tool_log\log\writer', $store);
        $this->assertTrue($store->is_logging());

        $logs = $DB->get_records('logstore_standard_log', array(), 'id ASC');
        $this->assertCount(0, $logs);

        $this->setCurrentTimeStart();

        $this->setUser(0);
        $event1 = \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)));
        $event1->trigger();

        $logs = $DB->get_records('logstore_standard_log', array(), 'id ASC');
        $this->assertCount(1, $logs);

        $log1 = reset($logs);
        unset($log1->id);
        $log1->other = unserialize($log1->other);
        $log1 = (array)$log1;
        $data = $event1->get_data();
        $data['origin'] = 'cli';
        $data['ip'] = null;
        $data['realuserid'] = null;
        $this->assertEquals($data, $log1);

        $this->setAdminUser();
        \core\session\manager::loginas($user1->id, context_system::instance());
        $this->assertEquals(2, $DB->count_records('logstore_standard_log'));

        logstore_standard_restore::hack_executing(1);
        $event2 = \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module2->cmid), 'other' => array('sample' => 6, 'xx' => 9)));
        $event2->trigger();
        logstore_standard_restore::hack_executing(0);

        \core\session\manager::init_empty_session();
        $this->assertFalse(\core\session\manager::is_loggedinas());

        $logs = $DB->get_records('logstore_standard_log', array(), 'id ASC');
        $this->assertCount(3, $logs);
        array_shift($logs);
        $log2 = array_shift($logs);
        $this->assertSame('\core\event\user_loggedinas', $log2->eventname);
        $this->assertSame('cli', $log2->origin);

        $log3 = array_shift($logs);
        unset($log3->id);
        $log3->other = unserialize($log3->other);
        $log3 = (array)$log3;
        $data = $event2->get_data();
        $data['origin'] = 'restore';
        $data['ip'] = null;
        $data['realuserid'] = 2;
        $this->assertEquals($data, $log3);

                $tablename = $store->get_internal_log_table_name();
        $this->assertTrue($DB->get_manager()->table_exists($tablename));

                $this->assertSame(3, $store->get_events_select_count('', array()));
        $events = $store->get_events_select('', array(), 'timecreated ASC', 0, 0);         $this->assertCount(3, $events);
        $resev1 = array_shift($events);
        array_shift($events);
        $resev2 = array_shift($events);
        $this->assertEquals($event1->get_data(), $resev1->get_data());
        $this->assertEquals($event2->get_data(), $resev2->get_data());

                set_config('buffersize', 3, 'logstore_standard');
        $manager = get_log_manager(true);
        $stores = $manager->get_readers();
        
        $store = $stores['logstore_standard'];
        $DB->delete_records('logstore_standard_log');

        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(0, $DB->count_records('logstore_standard_log'));
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(0, $DB->count_records('logstore_standard_log'));
        $store->flush();
        $this->assertEquals(2, $DB->count_records('logstore_standard_log'));
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(2, $DB->count_records('logstore_standard_log'));
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(2, $DB->count_records('logstore_standard_log'));
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(5, $DB->count_records('logstore_standard_log'));
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(5, $DB->count_records('logstore_standard_log'));
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(5, $DB->count_records('logstore_standard_log'));
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(8, $DB->count_records('logstore_standard_log'));

                set_config('logguests', 0, 'logstore_standard');
        set_config('buffersize', 0, 'logstore_standard');
        get_log_manager(true);
        $DB->delete_records('logstore_standard_log');
        get_log_manager(true);

        $this->setUser(null);
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(0, $DB->count_records('logstore_standard_log'));

        $this->setGuestUser();
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(0, $DB->count_records('logstore_standard_log'));

        $this->setUser($user1);
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(1, $DB->count_records('logstore_standard_log'));

        $this->setUser($user2);
        \logstore_standard\event\unittest_executed::create(
            array('context' => context_module::instance($module1->cmid), 'other' => array('sample' => 5, 'xx' => 10)))->trigger();
        $this->assertEquals(2, $DB->count_records('logstore_standard_log'));

        set_config('enabled_stores', '', 'tool_log');
        get_log_manager(true);
    }

    
    public function test_get_supported_reports() {
        $logmanager = get_log_manager();
        $allreports = \core_component::get_plugin_list('report');

        $supportedreports = array(
            'report_log' => '/report/log',
            'report_loglive' => '/report/loglive',
            'report_outline' => '/report/outline',
            'report_participation' => '/report/participation',
            'report_stats' => '/report/stats'
        );

                $expectedreports = array_keys(array_intersect_key($allreports, $supportedreports));
        $reports = $logmanager->get_supported_reports('logstore_standard');
        $reports = array_keys($reports);
        foreach ($expectedreports as $expectedreport) {
            $this->assertContains($expectedreport, $reports);
        }
    }

    
    public function test_events_traversable() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();
        $this->setAdminUser();

        set_config('enabled_stores', 'logstore_standard', 'tool_log');

        $manager = get_log_manager(true);
        $stores = $manager->get_readers();
        $store = $stores['logstore_standard'];

        $events = $store->get_events_select_iterator('', array(), '', 0, 0);
        $this->assertFalse($events->valid());

                        $events->close();

        $user = $this->getDataGenerator()->create_user();
        for ($i = 0; $i < 1000; $i++) {
            \core\event\user_created::create_from_userid($user->id)->trigger();
        }
        $store->flush();

                $this->assertEquals(1, iterator_count($store->get_events_select_iterator('', array(), '', 0, 1)));
        $this->assertEquals(2, iterator_count($store->get_events_select_iterator('', array(), '', 0, 2)));

        $iterator = $store->get_events_select_iterator('', array(), '', 0, 500);
        $this->assertInstanceOf('\core\event\base', $iterator->current());
        $this->assertEquals(500, iterator_count($iterator));
        $iterator->close();

                $mem = memory_get_usage();
        $events = $store->get_events_select('', array(), '', 0, 0);
        $arraymemusage = memory_get_usage() - $mem;

        $mem = memory_get_usage();
        $eventsit = $store->get_events_select_iterator('', array(), '', 0, 0);
        $eventsit->close();
        $itmemusage = memory_get_usage() - $mem;

        $this->assertInstanceOf('\Traversable', $eventsit);

        $this->assertLessThan($arraymemusage / 10, $itmemusage);

        set_config('enabled_stores', '', 'tool_log');
        get_log_manager(true);
    }

    
    public function test_cleanup_task() {
        global $DB;

        $this->resetAfterTest();

                $ctx = context_course::instance(1);
        $record = (object) array(
            'edulevel' => 0,
            'contextid' => $ctx->id,
            'contextlevel' => $ctx->contextlevel,
            'contextinstanceid' => $ctx->instanceid,
            'userid' => 1,
            'timecreated' => time(),
        );
        $DB->insert_record('logstore_standard_log', $record);
        $record->timecreated -= 3600 * 24 * 30;
        $DB->insert_record('logstore_standard_log', $record);
        $record->timecreated -= 3600 * 24 * 30;
        $DB->insert_record('logstore_standard_log', $record);
        $record->timecreated -= 3600 * 24 * 30;
        $DB->insert_record('logstore_standard_log', $record);
        $this->assertEquals(4, $DB->count_records('logstore_standard_log'));

                set_config('loglifetime', 1, 'logstore_standard');

        $this->expectOutputString(" Deleted old log records from standard store.\n");
        $clean = new \logstore_standard\task\cleanup_task();
        $clean->execute();

        $this->assertEquals(1, $DB->count_records('logstore_standard_log'));
    }
}
