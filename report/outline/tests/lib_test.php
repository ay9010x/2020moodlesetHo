<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class report_outline_lib_testcase extends advanced_testcase {

    
    private $user;

    
    private $course;

    
    private $tree;

    public function setUp() {
        $this->user = $this->getDataGenerator()->create_user();
        $this->user2 = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $this->tree = new \core_user\output\myprofile\tree();
        $this->resetAfterTest();
    }

    
    public function test_report_participation_supports_logstore() {
        $logmanager = get_log_manager();
        $allstores = \core_component::get_plugin_list_with_class('logstore', 'log\store');

        $supportedstores = array(
            'logstore_legacy' => '\logstore_legacy\log\store',
            'logstore_standard' => '\logstore_standard\log\store'
        );

                $expectedstores = array_keys(array_intersect($allstores, $supportedstores));
        $stores = $logmanager->get_supported_logstores('report_outline');
        $stores = array_keys($stores);
        foreach ($expectedstores as $expectedstore) {
            $this->assertContains($expectedstore, $stores);
        }
    }

    
    public function test_report_outline_myprofile_navigation() {
        $this->setAdminUser();
        $iscurrentuser = false;

        report_outline_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('outline', $nodes->getValue($this->tree));
        $this->assertArrayHasKey('complete', $nodes->getValue($this->tree));
    }

    
    public function test_report_outline_myprofile_navigation_without_permission() {
        $this->setUser($this->user);
        $iscurrentuser = true;

        report_outline_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('outline', $nodes->getValue($this->tree));
        $this->assertArrayNotHasKey('complete', $nodes->getValue($this->tree));
    }
}
