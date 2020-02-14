<?php



defined('MOODLE_INTERNAL') || die();


class tool_monitor_events_testcase extends advanced_testcase {

    
    public function setUp() {
        set_config('enablemonitor', 1, 'tool_monitor');
        $this->resetAfterTest();
    }

    
    public function test_rule_created() {
                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

                $ruledata = new stdClass();
        $ruledata->userid = $user->id;
        $ruledata->courseid = $course->id;
        $ruledata->plugin = 'mod_assign';
        $ruledata->eventname = '\mod_assign\event\submission_viewed';
        $ruledata->description = 'Rule description';
        $ruledata->descriptionformat = FORMAT_HTML;
        $ruledata->template = 'A message template';
        $ruledata->templateformat = FORMAT_HTML;
        $ruledata->frequency = 1;
        $ruledata->timewindow = 60;

                $sink = $this->redirectEvents();
        $rule = \tool_monitor\rule_manager::add_rule($ruledata);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\rule_created', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($rule->id, $event->objectid);
        $this->assertEventContextNotUsed($event);

                $ruledata->courseid = 0;

                $sink = $this->redirectEvents();
        \tool_monitor\rule_manager::add_rule($ruledata);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\rule_created', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    
    public function test_rule_updated() {
                $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $course = $this->getDataGenerator()->create_course();

                $createrule = new stdClass();
        $createrule->courseid = $course->id;
        $rule = $monitorgenerator->create_rule($createrule);

                $sink = $this->redirectEvents();
        $updaterule = new stdClass();
        $updaterule->id = $rule->id;
        \tool_monitor\rule_manager::update_rule($updaterule);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\rule_updated', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($rule->id, $event->objectid);
        $this->assertEventContextNotUsed($event);

                $createrule->courseid = 0;
        $rule = $monitorgenerator->create_rule($createrule);

                $sink = $this->redirectEvents();
        $updaterule = new stdClass();
        $updaterule->id = $rule->id;
        \tool_monitor\rule_manager::update_rule($updaterule);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\rule_updated', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    
    public function test_rule_deleted() {
                $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $course = $this->getDataGenerator()->create_course();

                $createrule = new stdClass();
        $createrule->courseid = $course->id;
        $rule = $monitorgenerator->create_rule($createrule);

                $sink = $this->redirectEvents();
        \tool_monitor\rule_manager::delete_rule($rule->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\rule_deleted', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($rule->id, $event->objectid);
        $this->assertEventContextNotUsed($event);

                $createrule = new stdClass();
        $createrule->courseid = 0;
        $rule = $monitorgenerator->create_rule($createrule);

                $sink = $this->redirectEvents();
        \tool_monitor\rule_manager::delete_rule($rule->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\rule_deleted', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    
    public function test_subscription_created() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

                $rule = $monitorgenerator->create_rule();

                $sink = $this->redirectEvents();
        $subscriptionid = \tool_monitor\subscription_manager::create_subscription($rule->id, $course->id, 0, $user->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\subscription_created', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($subscriptionid, $event->objectid);
        $this->assertEventContextNotUsed($event);

                $sink = $this->redirectEvents();
        \tool_monitor\subscription_manager::create_subscription($rule->id, 0, 0, $user->id);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\subscription_created', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
    }

    
    public function test_subscription_deleted() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

                $rule = $monitorgenerator->create_rule();

        $sub = new stdClass();
        $sub->courseid = $course->id;
        $sub->userid = $user->id;
        $sub->ruleid = $rule->id;

                $subscription = $monitorgenerator->create_subscription($sub);

                $sink = $this->redirectEvents();
        \tool_monitor\subscription_manager::delete_subscription($subscription->id, false);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\subscription_deleted', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEquals($subscription->id, $event->objectid);
        $this->assertEventContextNotUsed($event);

                $sub = new stdClass();
        $sub->courseid = 0;
        $sub->userid = $user->id;
        $sub->ruleid = $rule->id;

                $subscription = $monitorgenerator->create_subscription($sub);

                $sink = $this->redirectEvents();
        \tool_monitor\subscription_manager::delete_subscription($subscription->id, false);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\subscription_deleted', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());

                $subids = array();
        $sub->courseid = $course->id;
        for ($i = 1; $i <= 10; $i++) {
            $sub->userid = $i;
            $subscription = $monitorgenerator->create_subscription($sub);
            $subids[$subscription->id] = $subscription;
        }

                $sink = $this->redirectEvents();
        \tool_monitor\subscription_manager::remove_all_subscriptions_for_rule($rule->id);
        $events = $sink->get_events();

                $this->assertCount(10, $events);

                foreach ($events as $event) {
            $this->assertInstanceOf('\tool_monitor\event\subscription_deleted', $event);
            $this->assertEquals(context_course::instance($course->id), $event->get_context());
            $this->assertEventContextNotUsed($event);
            $this->assertArrayHasKey($event->objectid, $subids);
            unset($subids[$event->objectid]);
        }

                $this->assertEmpty($subids);
    }

    
    public function test_subscription_criteria_met() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $chapter = $bookgenerator->create_chapter(array('bookid' => $book->id));
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

                $rule = new stdClass();
        $rule->userid = $user->id;
        $rule->courseid = $course->id;
        $rule->plugin = 'mod_book';
        $rule->eventname = '\mod_book\event\chapter_viewed';
        $rule->frequency = 1;
        $rule->timewindow = 60;
        $rule = $monitorgenerator->create_rule($rule);

                $sub = new stdClass();
        $sub->courseid = $course->id;
        $sub->userid = $user->id;
        $sub->ruleid = $rule->id;
        $monitorgenerator->create_subscription($sub);

                $context = context_module::instance($book->cmid);
        $event = \mod_book\event\chapter_viewed::create_from_chapter($book, $context, $chapter);

                $sink = $this->redirectEvents();
        \tool_monitor\eventobservers::process_event($event);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\tool_monitor\event\subscription_criteria_met', $event);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $this->assertEventContextNotUsed($event);
    }
}
