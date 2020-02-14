<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class tool_monitor_task_clean_events_testcase extends advanced_testcase {

    
    public function setUp() {
        set_config('enablemonitor', 1, 'tool_monitor');
        $this->resetAfterTest(true);
    }

    
    public function test_clean_events() {
        global $DB;

                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $bookgenerator = $this->getDataGenerator()->get_plugin_generator('mod_book');
        $book = $this->getDataGenerator()->create_module('book', array('course' => $course->id));
        $bookcontext = context_module::instance($book->cmid);
        $bookchapter = $bookgenerator->create_chapter(array('bookid' => $book->id));
        $course2 = $this->getDataGenerator()->create_course();
        $book2 = $this->getDataGenerator()->create_module('book', array('course' => $course2->id));
        $book2context = context_module::instance($book2->cmid);
        $book2chapter = $bookgenerator->create_chapter(array('bookid' => $book2->id));
        $monitorgenerator = $this->getDataGenerator()->get_plugin_generator('tool_monitor');

                $rule = new stdClass();
        $rule->userid = $user->id;
        $rule->courseid = $course->id;
        $rule->plugin = 'mod_book';
        $rule->eventname = '\mod_book\event\course_module_viewed';
        $rule->timewindow = 500;

                $rule1 = $monitorgenerator->create_rule($rule);

        $rule->eventname = '\mod_book\event\course_module_instance_list_viewed';
        $rule2 = $monitorgenerator->create_rule($rule);

                        $rule->eventname = '\mod_book\event\course_module_viewed';
        $rule->timewindow = 200;
        $rule3 = $monitorgenerator->create_rule($rule);

        $rule->eventname = '\mod_book\event\course_module_instance_list_viewed';
        $rule4 = $monitorgenerator->create_rule($rule);

                $rule->courseid = $course2->id;
        $rule->eventname = '\mod_book\event\chapter_viewed';
        $rule->timewindow = 200;
        $rule5 = $monitorgenerator->create_rule($rule);

                $rule->courseid = 0;
        $rule->eventname = '\mod_book\event\chapter_viewed';
        $rule->timewindow = 500;
        $rule6 = $monitorgenerator->create_rule($rule);


                $sub = new stdClass;
        $sub->courseid = $course->id;
        $sub->ruleid = $rule1->id;
        $sub->userid = $user->id;
        $monitorgenerator->create_subscription($sub);

        $sub->ruleid = $rule2->id;
        $monitorgenerator->create_subscription($sub);

        $sub->ruleid = $rule3->id;
        $monitorgenerator->create_subscription($sub);

        $sub->ruleid = $rule4->id;
        $monitorgenerator->create_subscription($sub);

        $sub->ruleid = $rule5->id;
        $sub->courseid = $course2->id;
        $monitorgenerator->create_subscription($sub);

        $sub->ruleid = $rule6->id;
        $sub->courseid = 0;
        $monitorgenerator->create_subscription($sub);

                \mod_book\event\course_module_viewed::create_from_book($book, $bookcontext)->trigger();
        \mod_book\event\course_module_instance_list_viewed::create_from_course($course)->trigger();
        \mod_book\event\chapter_viewed::create_from_chapter($book, $bookcontext, $bookchapter)->trigger();

                        \mod_book\event\course_module_viewed::create_from_book($book2, $book2context)->trigger();
        \mod_book\event\course_module_instance_list_viewed::create_from_course($course2)->trigger();
                \mod_book\event\chapter_viewed::create_from_chapter($book2, $book2context, $book2chapter)->trigger();

                $eventparams = array(
            'context' => context_course::instance($course->id)
        );
        for ($i = 0; $i < 5; $i++) {
            \mod_quiz\event\course_module_instance_list_viewed::create($eventparams)->trigger();
            \mod_scorm\event\course_module_instance_list_viewed::create($eventparams)->trigger();
        }

                $this->assertEquals(4, $DB->count_records('tool_monitor_events'));

                        $task = new \tool_monitor\task\clean_events();
        $task->execute();

        $events = $DB->get_records('tool_monitor_events', array(), 'id');
        $this->assertEquals(4, count($events));
        $event1 = array_shift($events);
        $event2 = array_shift($events);
        $event3 = array_shift($events);
        $event4 = array_shift($events);
        $this->assertEquals('\mod_book\event\course_module_viewed', $event1->eventname);
        $this->assertEquals($course->id, $event1->courseid);
        $this->assertEquals('\mod_book\event\course_module_instance_list_viewed', $event2->eventname);
        $this->assertEquals($course->id, $event2->courseid);
        $this->assertEquals('\mod_book\event\chapter_viewed', $event3->eventname);
        $this->assertEquals($course->id, $event3->courseid);
        $this->assertEquals('\mod_book\event\chapter_viewed', $event4->eventname);
        $this->assertEquals($course2->id, $event4->courseid);

                $updaterule = new stdClass();
        $updaterule->id = $rule1->id;
        $updaterule->timewindow = 0;
        \tool_monitor\rule_manager::update_rule($updaterule);
        $updaterule->id = $rule2->id;
        \tool_monitor\rule_manager::update_rule($updaterule);

                $task = new \tool_monitor\task\clean_events();
        $task->execute();

        $this->assertEquals(4, $DB->count_records('tool_monitor_events'));

                \tool_monitor\rule_manager::delete_rule($rule1->id);
        \tool_monitor\rule_manager::delete_rule($rule2->id);
        \tool_monitor\rule_manager::delete_rule($rule3->id);
        \tool_monitor\rule_manager::delete_rule($rule4->id);

                $task = new \tool_monitor\task\clean_events();
        $task->execute();

                $events = $DB->get_records('tool_monitor_events', array(), 'id');
        $this->assertEquals(2, count($events));
        $event1 = array_shift($events);
        $event2 = array_shift($events);
        $this->assertEquals('\mod_book\event\chapter_viewed', $event1->eventname);
        $this->assertEquals($course->id, $event1->courseid);
        $this->assertEquals('\mod_book\event\chapter_viewed', $event2->eventname);
        $this->assertEquals($course2->id, $event2->courseid);

                $updaterule->id = $rule5->id;
        \tool_monitor\rule_manager::update_rule($updaterule);

                $task = new \tool_monitor\task\clean_events();
        $task->execute();

                $this->assertEquals(2, $DB->count_records('tool_monitor_events'));

                $updaterule->id = $rule5->id;
        $updaterule->timewindow = 500;
        \tool_monitor\rule_manager::update_rule($updaterule);

                $updaterule->id = $rule6->id;
        $updaterule->timewindow = 0;
        \tool_monitor\rule_manager::update_rule($updaterule);

                $task = new \tool_monitor\task\clean_events();
        $task->execute();

                $events = $DB->get_records('tool_monitor_events');
        $this->assertEquals(1, count($events));
        $event1 = array_shift($events);
        $this->assertEquals('\mod_book\event\chapter_viewed', $event1->eventname);
        $this->assertEquals($course2->id, $event1->courseid);

                \tool_monitor\rule_manager::delete_rule($rule6->id);

                \tool_monitor\rule_manager::delete_rule($rule5->id);

                $task = new \tool_monitor\task\clean_events();
        $task->execute();

                $this->assertEquals(0, $DB->count_records('tool_monitor_events'));
    }
}
