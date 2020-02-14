<?php



defined('MOODLE_INTERNAL') || die;

class tool_log_manager_testcase extends advanced_testcase {
    public function test_get_log_manager() {
        global $CFG;
        $this->resetAfterTest();

        $manager = get_log_manager();
        $this->assertInstanceOf('core\log\manager', $manager);

        $stores = $manager->get_readers();
        $this->assertInternalType('array', $stores);
        $this->assertCount(0, $stores);

        $this->assertFileExists("$CFG->dirroot/$CFG->admin/tool/log/store/standard/version.php");
        $this->assertFileExists("$CFG->dirroot/$CFG->admin/tool/log/store/legacy/version.php");

        set_config('enabled_stores', 'logstore_standard,logstore_legacy', 'tool_log');
        $manager = get_log_manager(true);
        $this->assertInstanceOf('core\log\manager', $manager);

        $stores = $manager->get_readers();
        $this->assertInternalType('array', $stores);
        $this->assertCount(2, $stores);
        foreach ($stores as $key => $store) {
            $this->assertInternalType('string', $key);
            $this->assertInstanceOf('core\log\sql_reader', $store);
        }

        $stores = $manager->get_readers('core\log\sql_internal_table_reader');
        $this->assertInternalType('array', $stores);
        $this->assertCount(1, $stores);
        foreach ($stores as $key => $store) {
            $this->assertInternalType('string', $key);
            $this->assertSame('logstore_standard', $key);
            $this->assertInstanceOf('core\log\sql_internal_table_reader', $store);
        }

        $stores = $manager->get_readers('core\log\sql_reader');
        $this->assertInternalType('array', $stores);
        $this->assertCount(2, $stores);
        foreach ($stores as $key => $store) {
            $this->assertInternalType('string', $key);
            $this->assertInstanceOf('core\log\sql_reader', $store);
        }
    }
}
