<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blog/locallib.php');
require_once($CFG->dirroot . '/blog/lib.php');


class tool_monitor_eventobservers_testcase extends advanced_testcase {
    
    public function setUp() {
                set_config('enablemonitor', 1, 'tool_monitor');
    }

    
    public function test_course_deleted() {
        global $DB;

        $this->setAdminUser();
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

        $rule = new stdClass();
        $rule->userid = $user->id;
        $rule->courseid = $course1->id;
        $rule->plugin = 'test';

        $sub = new stdClass();
        $sub->courseid = $course1->id;
        $sub->userid = $user->id;

                for ($i = 0; $i < 10; $i++) {
            $createdrule = $monitorgenerator->create_rule($rule);
            $sub->ruleid = $createdrule->id;
            $monitorgenerator->create_subscription($sub);
        }

                $rule->courseid = $course2->id;
        for ($i = 0; $i < 10; $i++) {
            $createdrule = $monitorgenerator->create_rule($rule);
            $sub->courseid = $rule->courseid;
            $sub->ruleid = $createdrule->id;
            $monitorgenerator->create_subscription($sub);
        }

                $rule = new stdClass();
        $rule->userid = $user->id;
        $rule->courseid = 0;
        $rule->plugin = 'core';
        $monitorgenerator->create_rule($rule);

                $courserules = \tool_monitor\rule_manager::get_rules_by_courseid($course1->id);
        $this->assertCount(11, $courserules);

                $totalrules = \tool_monitor\rule_manager::get_rules_by_plugin('test');
        $this->assertCount(20, $totalrules);
        $courserules = \tool_monitor\rule_manager::get_rules_by_courseid($course1->id, 0, 0, false);
        $this->assertCount(10, $courserules);
        $this->assertEquals(20, $DB->count_records('tool_monitor_subscriptions'));
        $coursesubs = \tool_monitor\subscription_manager::get_user_subscriptions_for_course($course1->id, 0, 0, $user->id);
        $this->assertCount(10, $coursesubs);

                delete_course($course1->id, false);

                $this->assertEquals(1, $DB->count_records('tool_monitor_rules', array('courseid' => 0)));

                $totalrules = \tool_monitor\rule_manager::get_rules_by_plugin('test');
        $this->assertCount(10, $totalrules);
        $courserules = \tool_monitor\rule_manager::get_rules_by_courseid($course1->id, 0, 0, false);
        $this->assertCount(0, $courserules);         $this->assertEquals(10, $DB->count_records('tool_monitor_subscriptions'));
        $coursesubs = \tool_monitor\subscription_manager::get_user_subscriptions_for_course($course1->id, 0, 0, $user->id);
        $this->assertCount(0, $coursesubs);     }

    
    public function test_flush() {
        global $DB;

        $this->resetAfterTest();

                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

                        $eventparams = array(
            'context' => context_course::instance($course->id)
        );
        for ($i = 0; $i < 5; $i++) {
            \core\event\course_viewed::create($eventparams)->trigger();
            \mod_quiz\event\course_module_instance_list_viewed::create($eventparams)->trigger();
            \mod_scorm\event\course_module_instance_list_viewed::create($eventparams)->trigger();
        }

                        $this->assertEquals(0, $DB->count_records('tool_monitor_events'));

                $rule = new stdClass();
        $rule->courseid = $course->id;
        $rule->plugin = 'mod_book';
        $rule->eventname = '\mod_book\event\course_module_instance_list_viewed';
        $rule = $monitorgenerator->create_rule($rule);

                $sub = new stdClass;
        $sub->courseid = $course->id;
        $sub->ruleid = $rule->id;
        $sub->userid = $user->id;
        $monitorgenerator->create_subscription($sub);

                for ($i = 0; $i < 5; $i++) {
            \core\event\course_viewed::create($eventparams)->trigger();
            \mod_quiz\event\course_module_instance_list_viewed::create($eventparams)->trigger();
            \mod_scorm\event\course_module_instance_list_viewed::create($eventparams)->trigger();
        }

                $event = \mod_book\event\course_module_instance_list_viewed::create_from_course($course);
        $event->trigger();

                $events = $DB->get_records('tool_monitor_events');
        $this->assertEquals(1, count($events));
        $monitorevent = array_pop($events);
        $this->assertEquals($event->eventname, $monitorevent->eventname);
        $this->assertEquals($event->contextid, $monitorevent->contextid);
        $this->assertEquals($event->contextlevel, $monitorevent->contextlevel);
        $this->assertEquals($event->get_url()->out(), $monitorevent->link);
        $this->assertEquals($event->courseid, $monitorevent->courseid);
        $this->assertEquals($event->timecreated, $monitorevent->timecreated);

                $DB->delete_records('tool_monitor_events');

                $rule = new stdClass();
        $rule->courseid = 0;
        $rule->plugin = 'mod_book';
        $rule->eventname = '\mod_book\event\course_module_instance_list_viewed';
        $rule = $monitorgenerator->create_rule($rule);

                $sub = new stdClass;
        $sub->courseid = 0;
        $sub->ruleid = $rule->id;
        $sub->userid = $user->id;
        $monitorgenerator->create_subscription($sub);

                $event = \mod_book\event\course_module_instance_list_viewed::create_from_course($course2);
        $event->trigger();

                $events = $DB->get_records('tool_monitor_events');
        $this->assertEquals(1, count($events));
        $monitorevent = array_pop($events);
        $this->assertEquals($event->eventname, $monitorevent->eventname);
        $this->assertEquals($event->contextid, $monitorevent->contextid);
        $this->assertEquals($event->contextlevel, $monitorevent->contextlevel);
        $this->assertEquals($event->get_url()->out(), $monitorevent->link);
        $this->assertEquals($event->courseid, $monitorevent->courseid);
        $this->assertEquals($event->timecreated, $monitorevent->timecreated);
    }

    
    public function test_process_event() {

        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $msgsink = $this->redirectMessages();

                $course = $this->getDataGenerator()->create_course();
        $toolgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

        $rulerecord = new stdClass();
        $rulerecord->courseid = $course->id;
        $rulerecord->eventname = '\mod_book\event\course_module_instance_list_viewed';
        $rulerecord->frequency = 1;

        $rule = $toolgenerator->create_rule($rulerecord);

        $subrecord = new stdClass();
        $subrecord->courseid = $course->id;
        $subrecord->ruleid = $rule->id;
        $subrecord->userid = $USER->id;
        $toolgenerator->create_subscription($subrecord);

        $recordexists = $DB->record_exists('task_adhoc', array('component' => 'tool_monitor'));
        $this->assertFalse($recordexists);

                $event = \mod_book\event\course_module_instance_list_viewed::create_from_course($course);
        $event->trigger();

        $this->verify_processed_data($msgsink);

                \tool_monitor\rule_manager::delete_rule($rule->id);
        $DB->delete_records('tool_monitor_events');

                $rulerecord->frequency = 5;
        $rule = $toolgenerator->create_rule($rulerecord);
        $subrecord->ruleid = $rule->id;
        $toolgenerator->create_subscription($subrecord);

                for ($i = 0; $i < 5; $i++) {
            $event = \mod_book\event\course_module_instance_list_viewed::create_from_course($course);
            $event->trigger();
            if ($i != 4) {
                $this->verify_message_not_sent_yet($msgsink);
            }
        }

        $this->verify_processed_data($msgsink);

                \tool_monitor\rule_manager::delete_rule($rule->id);
        $DB->delete_records('tool_monitor_events');

                $cm = new stdClass();
        $cm->course = $course->id;
        $book = $this->getDataGenerator()->create_module('book', $cm);
        $rulerecord->eventname = '\mod_book\event\course_module_viewed';
        $rulerecord->cmid = $book->cmid;
        $rule = $toolgenerator->create_rule($rulerecord);
        $subrecord->ruleid = $rule->id;
        $toolgenerator->create_subscription($subrecord);

                $params = array(
            'context' => context_module::instance($book->cmid),
            'objectid' => $book->id
        );
        for ($i = 0; $i < 5; $i++) {
            $event = \mod_book\event\course_module_viewed::create($params);
            $event->trigger();
            if ($i != 4) {
                $this->verify_message_not_sent_yet($msgsink);
            }
        }

        $this->verify_processed_data($msgsink);

                \tool_monitor\rule_manager::delete_rule($rule->id);
        $DB->delete_records('tool_monitor_events');

                $rulerecord->eventname = '\core\event\course_category_created';
        $rulerecord->courseid = 0;
        $rule = $toolgenerator->create_rule($rulerecord);
        $subrecord->courseid = 0;
        $subrecord->ruleid = $rule->id;
        $toolgenerator->create_subscription($subrecord);

                for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->create_category();
            if ($i != 4) {
                $this->verify_message_not_sent_yet($msgsink);
            }
        }
        $this->verify_processed_data($msgsink);

                \tool_monitor\rule_manager::delete_rule($rule->id);
        $DB->delete_records('tool_monitor_events');

                $rulerecord->eventname = '\core\event\blog_entry_created';
        $rulerecord->courseid = 0;
        $rule = $toolgenerator->create_rule($rulerecord);
        $subrecord->courseid = 0;
        $subrecord->ruleid = $rule->id;
        $toolgenerator->create_subscription($subrecord);

                $blog = new blog_entry();
        $blog->subject = "Subject of blog";
        $blog->userid = $USER->id;
        $states = blog_entry::get_applicable_publish_states();
        $blog->publishstate = reset($states);
        for ($i = 0; $i < 5; $i++) {
            $newblog = fullclone($blog);
            $newblog->add();
            if ($i != 4) {
                $this->verify_message_not_sent_yet($msgsink);
            }
        }

        $this->verify_processed_data($msgsink);
    }

    
    public function test_multiple_notification_not_sent() {
        global $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $messagesink = $this->redirectMessages();

                $course = $this->getDataGenerator()->create_course();
        $toolgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

        $rulerecord = new stdClass();
        $rulerecord->courseid = $course->id;
        $rulerecord->eventname = '\mod_book\event\course_module_instance_list_viewed';
        $rulerecord->frequency = 5;

        $rule = $toolgenerator->create_rule($rulerecord);

        $subrecord = new stdClass();
        $subrecord->courseid = $course->id;
        $subrecord->ruleid = $rule->id;
        $subrecord->userid = $USER->id;
        $toolgenerator->create_subscription($subrecord);

        for ($i = 0; $i < 7; $i++) {
                        $event = \mod_book\event\course_module_instance_list_viewed::create_from_course($course);
            $event->trigger();
            $this->waitForSecond();         }
        $this->run_adhock_tasks();
        $messages = $messagesink->get_messages();
        $this->assertCount(1, $messages);         for ($i = 0; $i < 3; $i++) {
                        $event = \mod_book\event\course_module_instance_list_viewed::create_from_course($course);
            $event->trigger();
        }

        $this->run_adhock_tasks();
        $messages = $messagesink->get_messages();
        $this->assertCount(2, $messages);     }

    
    protected function run_adhock_tasks() {
        while ($task = \core\task\manager::get_next_adhoc_task(time())) {
            $task->execute();
            \core\task\manager::adhoc_task_complete($task);
        }
        $this->expectOutputRegex("/^Sending message to the user with id \d+ for the subscription with id \d+\.\.\..Sent./ms");
    }

    
    protected function verify_processed_data(phpunit_message_sink $msgsink) {
        global $DB, $USER;

        $recordexists = $DB->count_records('task_adhoc', array('component' => 'tool_monitor'));
        $this->assertEquals(1, $recordexists);         $this->run_adhock_tasks();
        $this->assertEquals(1, $msgsink->count());
        $msgs = $msgsink->get_messages();
        $msg = array_pop($msgs);
        $this->assertEquals($USER->id, $msg->useridto);
        $this->assertEquals(1, $msg->notification);
        $msgsink->clear();
    }

    
    protected function verify_message_not_sent_yet(phpunit_message_sink $msgsink) {
        $msgs = $msgsink->get_messages();
        $this->assertCount(0, $msgs);
        $msgsink->clear();
    }

    
    public function test_replace_placeholders() {
        global $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $msgsink = $this->redirectMessages();

                $course = $this->getDataGenerator()->create_course();
        $toolgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');
        $context = \context_user::instance($USER->id, IGNORE_MISSING);

                $cm = new stdClass();
        $cm->course = $course->id;
        $book = $this->getDataGenerator()->create_module('book', $cm);

                $rulerecord = new stdClass();
        $rulerecord->courseid = $course->id;
        $rulerecord->eventname = '\mod_book\event\course_module_viewed';
        $rulerecord->cmid = $book->cmid;
        $rulerecord->frequency = 1;
        $rulerecord->template = '## {link} ##

* {modulelink}
* __{rulename}__
* {description}
* {eventname}';
        $rulerecord->templateformat = FORMAT_MARKDOWN;

        $rule = $toolgenerator->create_rule($rulerecord);

                $subrecord = new stdClass();
        $subrecord->courseid = $course->id;
        $subrecord->ruleid = $rule->id;
        $subrecord->userid = $USER->id;
        $toolgenerator->create_subscription($subrecord);

                $params = array(
            'context' => context_module::instance($book->cmid),
            'objectid' => $book->id
        );

        $event = \mod_book\event\course_module_viewed::create($params);
        $event->trigger();
        $this->run_adhock_tasks();
        $msgs = $msgsink->get_messages();
        $msg = array_pop($msgs);

        $modurl = new moodle_url('/mod/book/view.php', array('id' => $book->cmid));

        $this->assertContains('<h2>'.$event->get_url()->out().'</h2>', $msg->fullmessagehtml);
        $this->assertContains('<li>'.$modurl->out().'</li>', $msg->fullmessagehtml);
        $this->assertContains('<li><strong>'.$rule->get_name($context).'</strong></li>', $msg->fullmessagehtml);
        $this->assertContains('<li>'.$rule->get_description($context).'</li>', $msg->fullmessagehtml);
        $this->assertContains('<li>'.$rule->get_event_name().'</li>', $msg->fullmessagehtml);

        $this->assertEquals(FORMAT_PLAIN, $msg->fullmessageformat);
        $this->assertNotContains('<h2>', $msg->fullmessage);
        $this->assertNotContains('##', $msg->fullmessage);
        $this->assertContains(strtoupper($event->get_url()->out()), $msg->fullmessage);
        $this->assertContains('* '.$modurl->out(), $msg->fullmessage);
        $this->assertContains('* '.strtoupper($rule->get_name($context)), $msg->fullmessage);
        $this->assertContains('* '.$rule->get_description($context), $msg->fullmessage);
        $this->assertContains('* '.$rule->get_event_name(), $msg->fullmessage);
    }

    
    public function test_user_deleted() {
        global $DB;

        $this->setAdminUser();
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

        $rule = new stdClass();
        $rule->userid = $user->id;
        $rule->courseid = $course1->id;
        $rule->plugin = 'test';

        $sub = new stdClass();
        $sub->courseid = $course1->id;
        $sub->userid = $user->id;

                for ($i = 0; $i < 10; $i++) {
            $createdrule = $monitorgenerator->create_rule($rule);
            $sub->ruleid = $createdrule->id;
            $monitorgenerator->create_subscription($sub);
        }

                $rule->courseid = $course2->id;
        for ($i = 0; $i < 10; $i++) {
            $createdrule = $monitorgenerator->create_rule($rule);
            $sub->courseid = $rule->courseid;
            $sub->ruleid = $createdrule->id;
            $monitorgenerator->create_subscription($sub);
        }

                $totalrules = \tool_monitor\rule_manager::get_rules_by_plugin('test');
        $this->assertCount(20, $totalrules);
        $totalsubs = $DB->get_records('tool_monitor_subscriptions');
        $this->assertCount(20, $totalsubs);

                delete_user($user);

                $totalrules = \tool_monitor\rule_manager::get_rules_by_plugin('test');
        $this->assertCount(20, $totalrules);
        $totalsubs = $DB->get_records('tool_monitor_subscriptions');
        $this->assertCount(0, $totalsubs);     }

    
    public function test_course_module_deleted() {
        global $DB;

        $this->setAdminUser();
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

                $cm = new stdClass();
        $cm->course = $course1->id;
        $book = $this->getDataGenerator()->create_module('book', $cm);

        $rule = new stdClass();
        $rule->userid = $user->id;
        $rule->courseid = $course1->id;
        $rule->plugin = 'test';

        $sub = new stdClass();
        $sub->courseid = $course1->id;
        $sub->userid = $user->id;
        $sub->cmid = $book->cmid;

                for ($i = 0; $i < 10; $i++) {
            $createdrule = $monitorgenerator->create_rule($rule);
            $sub->ruleid = $createdrule->id;
            $monitorgenerator->create_subscription($sub);
        }

                $rule->courseid = $course2->id;
        for ($i = 0; $i < 10; $i++) {
            $createdrule = $monitorgenerator->create_rule($rule);
            $sub->courseid = $rule->courseid;
            $sub->ruleid = $createdrule->id;
            $sub->cmid = 0;
            $monitorgenerator->create_subscription($sub);
        }

                $totalrules = \tool_monitor\rule_manager::get_rules_by_plugin('test');
        $this->assertCount(20, $totalrules);
        $totalsubs = $DB->get_records('tool_monitor_subscriptions');
        $this->assertCount(20, $totalsubs);

                course_delete_module($book->cmid);

                $totalrules = \tool_monitor\rule_manager::get_rules_by_plugin('test');
        $this->assertCount(20, $totalrules);
        $totalsubs = $DB->get_records('tool_monitor_subscriptions');
        $this->assertCount(10, $totalsubs);     }

}
