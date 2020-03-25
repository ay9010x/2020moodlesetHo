<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class report_log_lib_testcase extends advanced_testcase {

    
    private $user;

    
    private $course;

    
    private $tree;

    public function setUp() {
        $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $this->tree = new \core_user\output\myprofile\tree();
        $this->resetAfterTest();
    }

    
    public function test_report_log_supports_logstore() {
        $logmanager = get_log_manager();
        $allstores = \core_component::get_plugin_list_with_class('logstore', 'log\store');

        $supportedstores = array(
            'logstore_database' => '\logstore_database\log\store',
            'logstore_legacy' => '\logstore_legacy\log\store',
            'logstore_standard' => '\logstore_standard\log\store'
        );

                $expectedstores = array_keys(array_intersect($allstores, $supportedstores));
        $stores = $logmanager->get_supported_logstores('report_log');
        $stores = array_keys($stores);
        foreach ($expectedstores as $expectedstore) {
            $this->assertContains($expectedstore, $stores);
        }
    }

    
    public function test_report_log_myprofile_navigation() {
                $this->setAdminUser();
        $iscurrentuser = false;

                report_log_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('alllogs', $nodes->getValue($this->tree));
        $this->assertArrayHasKey('todayslogs', $nodes->getValue($this->tree));
    }

    
    public function test_report_log_myprofile_navigation_without_permission() {
                $this->setUser($this->user);
        $iscurrentuser = true;

                report_log_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('alllogs', $nodes->getValue($this->tree));
        $this->assertArrayNotHasKey('todayslogs', $nodes->getValue($this->tree));
    }
}
