<?php



use block_online_users\fetcher;

defined('MOODLE_INTERNAL') || die();


class block_online_users_testcase extends advanced_testcase {

    protected $data;

    
    protected function setUp() {

                $generator = $this->getDataGenerator()->get_plugin_generator('block_online_users');
        $this->data = $generator->create_logged_in_users();

                $this->resetAfterTest(true);
    }

    
    public function test_fetcher_course1_group_members() {
        global $CFG;

        $groupid = $this->data['group1']->id;
        $now = time();
        $timetoshowusers = $CFG->block_online_users_timetosee * 60;
        $context = context_course::instance($this->data['course1']->id);
        $courseid = $this->data['course1']->id;
        $onlineusers = new fetcher($groupid, $now, $timetoshowusers, $context, false, $courseid);

        $usercount = $onlineusers->count_users();
        $users = $onlineusers->get_users();
        $this->assertEquals(3, $usercount, 'There was a problem counting the number of online users in group 1');
        $this->assertEquals($usercount, count($users), 'There was a problem counting the number of online users in group 1');

        $groupid = $this->data['group2']->id;
        $onlineusers = new fetcher($groupid, $now, $timetoshowusers, $context, false, $courseid);

        $usercount = $onlineusers->count_users();
        $users = $onlineusers->get_users();
        $this->assertEquals($usercount, count($users), 'There was a problem counting the number of online users in group 2');
        $this->assertEquals(4, $usercount, 'There was a problem counting the number of online users in group 2');

        $groupid = $this->data['group3']->id;
        $onlineusers = new fetcher($groupid, $now, $timetoshowusers, $context, false, $courseid);

        $usercount = $onlineusers->count_users();
        $users = $onlineusers->get_users();
        $this->assertEquals($usercount, count($users), 'There was a problem counting the number of online users in group 3');
        $this->assertEquals(0, $usercount, 'There was a problem counting the number of online users in group 3');
    }

    
    public function test_fetcher_courses() {

        global $CFG;

        $currentgroup = null;
        $now = time();
        $timetoshowusers = $CFG->block_online_users_timetosee * 60;
        $context = context_course::instance($this->data['course1']->id);
        $courseid = $this->data['course1']->id;
        $onlineusers = new fetcher($currentgroup, $now, $timetoshowusers, $context, false, $courseid);

        $usercount = $onlineusers->count_users();
        $users = $onlineusers->get_users();
        $this->assertEquals($usercount, count($users), 'There was a problem counting the number of online users in course 1');
        $this->assertEquals(9, $usercount, 'There was a problem counting the number of online users in course 1');

        $courseid = $this->data['course2']->id;
        $onlineusers = new fetcher($currentgroup, $now, $timetoshowusers, $context, false, $courseid);

        $usercount = $onlineusers->count_users();
        $users = $onlineusers->get_users();
        $this->assertEquals($usercount, count($users), 'There was a problem counting the number of online users in course 2');
        $this->assertEquals(0, $usercount, 'There was a problem counting the number of online users in course 2');
    }

    
    public function test_fetcher_sitelevel() {
        global $CFG;

        $currentgroup = null;
        $now = time();
        $timetoshowusers = $CFG->block_online_users_timetosee * 60;
        $context = context_system::instance();
        $onlineusers = new fetcher($currentgroup, $now, $timetoshowusers, $context, true);

        $usercount = $onlineusers->count_users();
        $users = $onlineusers->get_users();
        $this->assertEquals($usercount, count($users), 'There was a problem counting the number of online users at site level');
        $this->assertEquals(12, $usercount, 'There was a problem counting the number of online users at site level');
    }
}
