<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/grade/report/user/lib.php');


class gradereport_user_lib_testcase extends advanced_testcase {

    
    private $user;

    
    private $course;

    
    private $tree;

    public function setUp() {
        $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $this->tree = new \core_user\output\myprofile\tree();
        $this->resetAfterTest();
    }

    
    public function test_gradereport_user_myprofile_navigation() {
        $this->setAdminUser();
        $iscurrentuser = false;

        gradereport_user_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('grade', $nodes->getValue($this->tree));
    }

    
    public function test_gradereport_user_myprofile_navigation_without_permission() {
        $this->setUser($this->user);
        $iscurrentuser = true;

        gradereport_user_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('grade', $nodes->getValue($this->tree));
    }
}
