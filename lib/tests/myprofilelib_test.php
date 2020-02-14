<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/myprofilelib.php');


class core_myprofilelib_testcase extends advanced_testcase {

    
    private $user;

    
    private $course;

    
    private $tree;

    public function setUp() {
                global $PAGE;
        $PAGE->set_url('/test');

        $this->user = $this->getDataGenerator()->create_user();
        $this->user2 = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();
        $this->tree = new \core_user\output\myprofile\tree();
        $this->resetAfterTest();
    }

    
    public function test_core_myprofile_navigation_as_admin() {
        $this->setAdminUser();
        $iscurrentuser = false;

                core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $categories = $reflector->getProperty('categories');
        $categories->setAccessible(true);
        $cats = $categories->getValue($this->tree);
        $this->assertArrayHasKey('contact', $cats);
        $this->assertArrayHasKey('coursedetails', $cats);
        $this->assertArrayHasKey('miscellaneous', $cats);
        $this->assertArrayHasKey('reports', $cats);
        $this->assertArrayHasKey('administration', $cats);
        $this->assertArrayHasKey('loginactivity', $cats);

        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('fullprofile', $nodes->getValue($this->tree));
    }

    
    public function test_core_myprofile_navigation_course_without_permission() {
        $this->setUser($this->user2);
        $iscurrentuser = false;

        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('fullprofile', $nodes->getValue($this->tree));
    }

    
    public function test_core_myprofile_navigation_profile_link_as_current_user() {
        $this->setUser($this->user);
        $iscurrentuser = true;

        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('editprofile', $nodes->getValue($this->tree));
    }

    
    public function test_core_myprofile_navigation_profile_link_as_admin() {
        $this->setAdminUser();
        $iscurrentuser = false;

        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('editprofile', $nodes->getValue($this->tree));
    }

    
    public function test_core_myprofile_navigation_preference_as_admin() {
        $this->setAdminUser();
        $iscurrentuser = false;

        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('preferences', $nodes->getValue($this->tree));
        $this->assertArrayHasKey('loginas', $nodes->getValue($this->tree));
    }

    
    public function test_core_myprofile_navigation_preference_without_permission() {
                $this->setUser($this->user2);
        $iscurrentuser = false;

        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, $this->course);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('loginas', $nodes->getValue($this->tree));
    }

    
    public function test_core_myprofile_navigation_contact_fields_as_admin() {
        global $CFG;

                set_config("hiddenuserfields", "country,city,webpage,icqnumber,skypeid,yahooid,aimid,msnid");
        set_config("showuseridentity", "email,address,phone1,phone2,institution,department,idnumber");
        $hiddenfields = explode(',', $CFG->hiddenuserfields);
        $identityfields = explode(',', $CFG->showuseridentity);
        $this->setAdminUser();
        $iscurrentuser = false;

                $fields = array(
            'country' => 'AU',
            'city' => 'Silent hill',
            'url' => 'Ghosts',
            'icq' => 'Wth is ICQ?',
            'skype' => 'derp',
            'yahoo' => 'are you living in the 90\'s?',
            'aim' => 'are you for real?',
            'msn' => '...',
            'email' => 'Rulelikeaboss@example.com',
            'address' => 'Didn\'t I mention silent hill already ?',
            'phone1' => '123',
            'phone2' => '234',
            'institution' => 'strange land',
            'department' => 'video game/movie',
            'idnumber' => 'SLHL'
        );
        foreach ($fields as $field => $value) {
            $this->user->$field = $value;
        }

                core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, null);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        foreach ($hiddenfields as $field) {
            $this->assertArrayHasKey($field, $nodes->getValue($this->tree));
        }
        foreach ($identityfields as $field) {
            $this->assertArrayHasKey($field, $nodes->getValue($this->tree));
        }
    }

    
    public function test_core_myprofile_navigation_contact_field_without_permission() {
        global $CFG;

        $iscurrentuser = false;
        $hiddenfields = explode(',', $CFG->hiddenuserfields);
        $identityfields = explode(',', $CFG->showuseridentity);

                $this->setUser($this->user2);
        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, null);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        foreach ($hiddenfields as $field) {
            $this->assertArrayNotHasKey($field, $nodes->getValue($this->tree));
        }
        foreach ($identityfields as $field) {
            $this->assertArrayNotHasKey($field, $nodes->getValue($this->tree));
        }
    }

    
    public function test_core_myprofile_navigation_login_activity() {
                $this->setAdminUser();
        $iscurrentuser = false;

        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, null);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('firstaccess', $nodes->getValue($this->tree));
        $this->assertArrayHasKey('lastaccess', $nodes->getValue($this->tree));
        $this->assertArrayHasKey('lastip', $nodes->getValue($this->tree));
    }

    
    public function test_core_myprofile_navigationn_login_activity_without_permission() {
                set_config("hiddenuserfields", "firstaccess,lastaccess,lastip");
        $this->setUser($this->user2);
        $iscurrentuser = false;

        core_myprofile_navigation($this->tree, $this->user, $iscurrentuser, null);
        $reflector = new ReflectionObject($this->tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('firstaccess', $nodes->getValue($this->tree));
        $this->assertArrayNotHasKey('lastaccess', $nodes->getValue($this->tree));
        $this->assertArrayNotHasKey('lastip', $nodes->getValue($this->tree));
    }
}
