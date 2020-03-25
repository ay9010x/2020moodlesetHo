<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot. '/report/usersessions/lib.php');


class report_usersessions_lib_testcase extends advanced_testcase {

    
    private $user;

    
    private $course;

    
    private $tree;

    public function setUp() {
        $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $this->tree = new \core_user\output\myprofile\tree();
        $this->resetAfterTest();
    }

    
    public function test_report_usersessions_myprofile_navigation_as_admin() {
        $this->setAdminUser();
        $iscurrentuser = false;

                report_usersessions_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('usersessions', $nodes->getValue($this->tree));
    }

    
    public function test_report_usersessions_myprofile_navigation_as_current_user() {
        $this->setUser($this->user);
        $iscurrentuser = true;

        report_usersessions_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('usersessions', $nodes->getValue($this->tree));
    }

    
    public function test_report_usersessions_myprofile_navigation_as_guest() {
        $this->setGuestUser();
        $iscurrentuser = true;

        report_usersessions_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('usersessions', $nodes->getValue($this->tree));
    }

    
    public function test_report_usersessions_myprofile_navigation_without_permission() {
                $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);
        $iscurrentuser = false;

        report_usersessions_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('usersessions', $nodes->getValue($this->tree));

    }
}
