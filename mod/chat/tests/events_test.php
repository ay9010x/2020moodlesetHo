<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/chat/lib.php');


class mod_chat_events_testcase extends advanced_testcase {

    public function test_message_sent() {
        global $DB;
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));
        $cm = $DB->get_record('course_modules', array('id' => $chat->cmid));

                $this->setUser($user1->id);
        $sid1 = chat_login_user($chat->id, 'ajax', 0, $course);

                $this->setUser($user2->id);
        $sid2 = chat_login_user($chat->id, 'ajax', 0, $course);

                $chatuser1 = $DB->get_record('chat_users', array('sid' => $sid1));
        $chatuser2 = $DB->get_record('chat_users', array('sid' => $sid2));

        $sink = $this->redirectEvents();

                        $this->setUser($user1->id);
        $messageid = chat_send_chatmessage($chatuser1, 'Hello!', false, $cm);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_chat\event\message_sent', $event);
        $this->assertEquals($messageid, $event->objectid);
        $this->assertEquals($user1->id, $event->relateduserid);
        $this->assertEquals($user1->id, $event->userid);
        $expected = array($course->id, 'chat', 'talk', "view.php?id=$cm->id", $chat->id, $cm->id, $user1->id);
        $this->assertEventLegacyLogData($expected, $event);

                        $sink->clear();
        $this->setUser($user2->id);
        $messageid = chat_send_chatmessage($chatuser2, 'Hello!');
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_chat\event\message_sent', $event);
        $this->assertEquals($messageid, $event->objectid);
        $this->assertEquals($user2->id, $event->relateduserid);
        $this->assertEquals($user2->id, $event->userid);
        $expected = array($course->id, 'chat', 'talk', "view.php?id=$cm->id", $chat->id, $cm->id, $user2->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

                $sink->clear();
        $this->setAdminUser();
        chat_send_chatmessage($chatuser1, 'enter', true);
        $this->assertEquals(0, $sink->count());

        $sink->close();
    }

    public function test_sessions_viewed() {
        global $USER;
        $this->resetAfterTest();

                        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($chat->cmid),
            'objectid' => $chat->id,
            'other' => array(
                'start' => 1234,
                'end' => 5678
            )
        );
        $event = \mod_chat\event\sessions_viewed::create($params);
        $event->add_record_snapshot('chat', $chat);
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\mod_chat\event\sessions_viewed', $event);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals(context_module::instance($chat->cmid), $event->get_context());
        $this->assertEquals(1234, $event->other['start']);
        $this->assertEquals(5678, $event->other['end']);
        $this->assertEquals($chat->id, $event->objectid);
        $this->assertEquals($chat, $event->get_record_snapshot('chat', $chat->id));
        $expected = array($course->id, 'chat', 'report', "report.php?id=$chat->cmid", $chat->id, $chat->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_course_module_instance_list_viewed() {
        global $USER;
        $this->resetAfterTest();

                        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $params = array(
            'context' => context_course::instance($course->id)
        );
        $event = \mod_chat\event\course_module_instance_list_viewed::create($params);
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\mod_chat\event\course_module_instance_list_viewed', $event);
        $this->assertEquals($USER->id, $event->userid);
        $this->assertEquals(context_course::instance($course->id), $event->get_context());
        $expected = array($course->id, 'chat', 'view all', "index.php?id=$course->id", '');
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_course_module_viewed() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('chat', $chat->id);
        $context = context_module::instance($cm->id);

        $params = array(
            'objectid' => $chat->id,
            'context' => $context
        );
        $event = \mod_chat\event\course_module_viewed::create($params);
        $event->add_record_snapshot('chat', $chat);
        $event->trigger();

        $expected = array($course->id, 'chat', 'view', "view.php?id=$cm->id", $chat->id, $cm->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);
        $url = new moodle_url('/mod/chat/view.php', array('id' => $cm->id));
        $this->assertEquals($url, $event->get_url());
        $event->get_name();
    }
}
