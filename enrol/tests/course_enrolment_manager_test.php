<?php



defined('MOODLE_INTERNAL') || die();



class core_course_enrolment_manager_testcase extends advanced_testcase {
    
    private $course = null;
    
    private $users = array();
    
    private $groups = array();

    
    protected function setUp() {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/locallib.php');
        $this->setAdminUser();

        $users = array();
        $groups = array();
                $course = $this->getDataGenerator()->create_course();
        $users['user0'] = $this->getDataGenerator()->create_user(
                array('username' => 'user0', 'firstname' => 'user0'));         $users['user1'] = $this->getDataGenerator()->create_user(
                array('username' => 'user1', 'firstname' => 'user1'));         $users['user21'] = $this->getDataGenerator()->create_user(
                array('username' => 'user21', 'firstname' => 'user21'));         $users['user22'] = $this->getDataGenerator()->create_user(
                array('username' => 'user22', 'firstname' => 'user22'));
        $users['userall'] = $this->getDataGenerator()->create_user(
                array('username' => 'userall', 'firstname' => 'userall'));         $users['usertch'] = $this->getDataGenerator()->create_user(
                array('username' => 'usertch', 'firstname' => 'usertch')); 
                $this->getDataGenerator()->enrol_user($users['user0']->id, $course->id, 'student');         $this->getDataGenerator()->enrol_user($users['user1']->id, $course->id, 'student');         $this->getDataGenerator()->enrol_user($users['user21']->id, $course->id, 'student');         $this->getDataGenerator()->enrol_user($users['user22']->id, $course->id, 'student', 'manual', 0, 0, ENROL_USER_SUSPENDED);         $this->getDataGenerator()->enrol_user($users['userall']->id, $course->id, 'student');         $this->getDataGenerator()->enrol_user($users['usertch']->id, $course->id, 'editingteacher'); 
                $groups['group1'] = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $groups['group2'] = $this->getDataGenerator()->create_group(array('courseid' => $course->id));

                $this->getDataGenerator()->create_group_member(
                array('groupid' => $groups['group1']->id, 'userid' => $users['user1']->id));
        $this->getDataGenerator()->create_group_member(
                array('groupid' => $groups['group2']->id, 'userid' => $users['user21']->id));
        $this->getDataGenerator()->create_group_member(
                array('groupid' => $groups['group2']->id, 'userid' => $users['user22']->id));
        $this->getDataGenerator()->create_group_member(
                array('groupid' => $groups['group1']->id, 'userid' => $users['userall']->id));
        $this->getDataGenerator()->create_group_member(
                array('groupid' => $groups['group2']->id, 'userid' => $users['userall']->id));

                $this->course = $course;
        $this->users = $users;
        $this->groups = $groups;
    }

    
    public function test_get_total_users() {
        global $PAGE;

        $this->resetAfterTest();

                $manager = new course_enrolment_manager($PAGE, $this->course);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(6, $totalusers, 'All users must be returned when no filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 5);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(5, $totalusers, 'Only students must be returned when student role filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 3);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(1, $totalusers, 'Only teacher must be returned when teacher role filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, 'userall');
        $totalusers = $manager->get_total_users();
        $this->assertEquals(1, $totalusers, 'Only searchable user must be returned when search filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', $this->groups['group1']->id);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(2, $totalusers, 'Only group members must be returned when group filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', $this->groups['group2']->id);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(3, $totalusers, 'Only group members must be returned when group filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', -1);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(2, $totalusers, 'Only non-group members must be returned when \'no groups\' filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', 0, ENROL_USER_ACTIVE);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(5, $totalusers, 'Only active users must be returned when active users filtering is applied.');

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', 0, ENROL_USER_SUSPENDED);
        $totalusers = $manager->get_total_users();
        $this->assertEquals(1, $totalusers, 'Only suspended users must be returned when suspended users filtering is applied.');
    }

    
    public function test_get_users() {
        global $PAGE;

        $this->resetAfterTest();

                $manager = new course_enrolment_manager($PAGE, $this->course);
        $users = $manager->get_users('id');
        $this->assertCount(6, $users,  'All users must be returned when no filtering is applied.');
        $this->assertArrayHasKey($this->users['user0']->id, $users);
        $this->assertArrayHasKey($this->users['user1']->id, $users);
        $this->assertArrayHasKey($this->users['user21']->id, $users);
        $this->assertArrayHasKey($this->users['user22']->id, $users);
        $this->assertArrayHasKey($this->users['userall']->id, $users);
        $this->assertArrayHasKey($this->users['usertch']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 5);
        $users = $manager->get_users('id');
        $this->assertCount(5, $users, 'Only students must be returned when student role filtering is applied.');
        $this->assertArrayHasKey($this->users['user0']->id, $users);
        $this->assertArrayHasKey($this->users['user1']->id, $users);
        $this->assertArrayHasKey($this->users['user21']->id, $users);
        $this->assertArrayHasKey($this->users['user22']->id, $users);
        $this->assertArrayHasKey($this->users['userall']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 3);
        $users = $manager->get_users('id');
        $this->assertCount(1, $users, 'Only teacher must be returned when teacher role filtering is applied.');
        $this->assertArrayHasKey($this->users['usertch']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, 'userall');
        $users = $manager->get_users('id');
        $this->assertCount(1, $users, 'Only searchable user must be returned when search filtering is applied.');
        $this->assertArrayHasKey($this->users['userall']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', $this->groups['group1']->id);
        $users = $manager->get_users('id');
        $this->assertCount(2, $users, 'Only group members must be returned when group filtering is applied.');
        $this->assertArrayHasKey($this->users['user1']->id, $users);
        $this->assertArrayHasKey($this->users['userall']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', $this->groups['group2']->id);
        $users = $manager->get_users('id');
        $this->assertCount(3, $users, 'Only group members must be returned when group filtering is applied.');
        $this->assertArrayHasKey($this->users['user21']->id, $users);
        $this->assertArrayHasKey($this->users['user22']->id, $users);
        $this->assertArrayHasKey($this->users['userall']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', -1);
        $users = $manager->get_users('id');
        $this->assertCount(2, $users, 'Only non-group members must be returned when \'no groups\' filtering is applied.');
        $this->assertArrayHasKey($this->users['user0']->id, $users);
        $this->assertArrayHasKey($this->users['usertch']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', 0, ENROL_USER_ACTIVE);
        $users = $manager->get_users('id');
        $this->assertCount(5, $users, 'Only active users must be returned when active users filtering is applied.');
        $this->assertArrayHasKey($this->users['user0']->id, $users);
        $this->assertArrayHasKey($this->users['user1']->id, $users);
        $this->assertArrayHasKey($this->users['user21']->id, $users);
        $this->assertArrayHasKey($this->users['userall']->id, $users);
        $this->assertArrayHasKey($this->users['usertch']->id, $users);

                $manager = new course_enrolment_manager($PAGE, $this->course, null, 0, '', 0, ENROL_USER_SUSPENDED);
        $users = $manager->get_users('id');
        $this->assertCount(1, $users, 'Only suspended users must be returned when suspended users filtering is applied.');
        $this->assertArrayHasKey($this->users['user22']->id, $users);
    }
}
