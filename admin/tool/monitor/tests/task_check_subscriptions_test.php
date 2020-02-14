<?php
defined('MOODLE_INTERNAL') || exit();


class tool_monitor_task_check_subscriptions_testcase extends advanced_testcase {

    private $course;
    private $user;
    private $rule;
    private $subscription;
    private $teacherrole;
    private $studentrole;

    
    public function setUp() {
        global $DB;
        set_config('enablemonitor', 1, 'tool_monitor');
        $this->resetAfterTest(true);

                $this->user = $this->getDataGenerator()->create_user();
        $this->course = $this->getDataGenerator()->create_course();

        $rule = new stdClass();
        $rule->userid = 2;         $rule->courseid = $this->course->id;
        $rule->plugin = 'mod_book';
        $rule->eventname = '\mod_book\event\course_module_viewed';
        $rule->timewindow = 500;
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $this->rule = $monitorgenerator->create_rule($rule);

        $sub = new stdClass();
        $sub->courseid = $this->course->id;
        $sub->userid = $this->user->id;
        $sub->ruleid = $this->rule->id;
        $this->subscription = $monitorgenerator->create_subscription($sub);

                $this->teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->studentrole = $DB->get_record('role', array('shortname' => 'student'));
    }

    
    private function reload_subscription() {
        global $DB;
        $sub = $DB->get_record('tool_monitor_subscriptions', array('id' => $this->subscription->id));
        $this->subscription = new \tool_monitor\subscription($sub);
    }

    
    public function test_task_name() {
        $task = new \tool_monitor\task\check_subscriptions();
        $this->assertEquals(get_string('taskchecksubscriptions', 'tool_monitor'), $task->get_name());
    }

    
    public function test_site_level_subscription() {
                $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $sub = new stdClass();
        $sub->userid = $this->user->id;
        $sub->ruleid = $this->rule->id;
        $this->subscription = $monitorgenerator->create_subscription($sub);

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $this->getDataGenerator()->role_assign($this->teacherrole->id, $this->user->id, context_system::instance());

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_module_disabled() {
        set_config('enablemonitor', 0, 'tool_monitor');

                $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_active_unaffected() {
                $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, $this->teacherrole->id);

                $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_course_enrolment() {
                        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, $this->teacherrole->id);

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_enrolled_user_with_no_capability() {
                $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, $this->studentrole->id);

                $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_can_access_course() {
                $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, $this->teacherrole->id);

                $context = \context_course::instance($this->course->id);
        assign_capability('moodle/course:viewhiddencourses', CAP_PROHIBIT, $this->teacherrole->id, $context);

                $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                course_change_visibility($this->course->id, false);

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_cm_access() {
                $context = \context_course::instance($this->course->id);
        assign_capability('tool/monitor:subscribe', CAP_ALLOW, $this->studentrole->id, $context);
        $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, $this->studentrole->id);

                $book = $this->getDataGenerator()->create_module('book', array('course' => $this->course->id));

                $sub = new stdClass();
        $sub->courseid = $this->course->id;
        $sub->userid = $this->user->id;
        $sub->ruleid = $this->rule->id;
        $sub->cmid = $book->cmid;
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $this->subscription = $monitorgenerator->create_subscription($sub);

                $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                set_coursemodule_visible($book->cmid, false);

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                set_coursemodule_visible($book->cmid, true);

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_stale_subscription_removal() {
        global $DB;
                $daysold = 1 + \tool_monitor\subscription_manager::INACTIVE_SUBSCRIPTION_LIFESPAN_IN_DAYS;

        $inactivedate = strtotime("-$daysold days", time());
        $DB->set_field('tool_monitor_subscriptions', 'inactivedate', $inactivedate, array('id' => $this->subscription->id));

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->assertEquals(false, $DB->record_exists('tool_monitor_subscriptions', array('id' => $this->subscription->id)));
    }

    
    public function test_user_not_fully_set_up() {
        global $DB;

                $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, $this->teacherrole->id);

                $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $DB->set_field('user', 'email', '', array('id' => $this->user->id));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }

    
    public function test_suspended_user() {
        global $DB;

                $this->getDataGenerator()->enrol_user($this->user->id, $this->course->id, $this->teacherrole->id);

                $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $DB->set_field('user', 'suspended', '1', array('id' => $this->user->id));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(false, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));

                $DB->set_field('user', 'suspended', '0', array('id' => $this->user->id));

                $task = new \tool_monitor\task\check_subscriptions();
        $task->execute();

                $this->reload_subscription();
        $this->assertEquals(true, \tool_monitor\subscription_manager::subscription_is_active($this->subscription));
    }
}
