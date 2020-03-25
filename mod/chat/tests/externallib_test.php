<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');


class mod_chat_external_testcase extends externallib_advanced_testcase {

    
    public function test_login_user() {
        global $DB;

        $this->resetAfterTest(true);

                $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        $result = mod_chat_external::login_user($chat->id);
        $result = external_api::clean_returnvalue(mod_chat_external::login_user_returns(), $result);

                $sid = $DB->get_field('chat_users', 'sid', array('userid' => $user->id, 'chatid' => $chat->id));
        $this->assertEquals($result['chatsid'], $sid);

    }

    
    public function test_get_chat_users() {
        global $DB;

        $this->resetAfterTest(true);

                $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $this->setUser($user1);
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, $studentrole->id);

        $result = mod_chat_external::login_user($chat->id);
        $result = external_api::clean_returnvalue(mod_chat_external::login_user_returns(), $result);

        $this->setUser($user2);
        $result = mod_chat_external::login_user($chat->id);
        $result = external_api::clean_returnvalue(mod_chat_external::login_user_returns(), $result);

                $result = mod_chat_external::get_chat_users($result['chatsid']);
        $result = external_api::clean_returnvalue(mod_chat_external::get_chat_users_returns(), $result);

                $this->assertCount(2, $result['users']);
        $found = 0;
        foreach ($result['users'] as $user) {
            if ($user['id'] == $user1->id or $user['id'] == $user2->id) {
                $found++;
            }
        }
        $this->assertEquals(2, $found);

    }

    
    public function test_send_get_chat_message() {
        global $DB;

        $this->resetAfterTest(true);

                $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

        $result = mod_chat_external::login_user($chat->id);
        $result = external_api::clean_returnvalue(mod_chat_external::login_user_returns(), $result);
        $chatsid = $result['chatsid'];

        $result = mod_chat_external::send_chat_message($chatsid, 'hello!');
        $result = external_api::clean_returnvalue(mod_chat_external::send_chat_message_returns(), $result);

        
        $result = mod_chat_external::get_chat_latest_messages($chatsid, 0);
        $result = external_api::clean_returnvalue(mod_chat_external::get_chat_latest_messages_returns(), $result);

        foreach ($result['messages'] as $message) {
                        if ($message['system']) {
                continue;
            }
            $this->assertEquals('hello!', $message['message']);
        }
    }

    
    public function test_view_chat() {
        global $DB;

        $this->resetAfterTest(true);

                $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $chat = $this->getDataGenerator()->create_module('chat', array('course' => $course->id));
        $context = context_module::instance($chat->cmid);
        $cm = get_coursemodule_from_instance('chat', $chat->id);

                try {
            mod_chat_external::view_chat(0);
            $this->fail('Exception expected due to invalid mod_chat instance id.');
        } catch (moodle_exception $e) {
            $this->assertEquals('invalidrecord', $e->errorcode);
        }

                $user = self::getDataGenerator()->create_user();
        $this->setUser($user);
        try {
            mod_chat_external::view_chat($chat->id);
            $this->fail('Exception expected due to not enrolled user.');
        } catch (moodle_exception $e) {
            $this->assertEquals('requireloginerror', $e->errorcode);
        }

                $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);

                $sink = $this->redirectEvents();

        $result = mod_chat_external::view_chat($chat->id);
        $result = external_api::clean_returnvalue(mod_chat_external::view_chat_returns(), $result);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_shift($events);

                $this->assertInstanceOf('\mod_chat\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodlechat = new \moodle_url('/mod/chat/view.php', array('id' => $cm->id));
        $this->assertEquals($moodlechat, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                        assign_capability('mod/chat:chat', CAP_PROHIBIT, $studentrole->id, $context->id);
        accesslib_clear_all_caches_for_unit_testing();

        try {
            mod_chat_external::view_chat($chat->id);
            $this->fail('Exception expected due to missing capability.');
        } catch (moodle_exception $e) {
            $this->assertEquals('nopermissions', $e->errorcode);
        }
    }

    
    public function test_get_chats_by_courses() {
        global $DB, $USER;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $course1 = self::getDataGenerator()->create_course();
        $chatoptions1 = array(
                              'course' => $course1->id,
                              'name' => 'First Chat'
                             );
        $chat1 = self::getDataGenerator()->create_module('chat', $chatoptions1);
        $course2 = self::getDataGenerator()->create_course();
        $chatoptions2 = array(
                              'course' => $course2->id,
                              'name' => 'Second Chat'
                             );
        $chat2 = self::getDataGenerator()->create_module('chat', $chatoptions2);
        $student1 = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                self::getDataGenerator()->enrol_user($student1->id,  $course1->id, $studentrole->id);
        $this->setUser($student1);

        $chats = mod_chat_external::get_chats_by_courses();
                $chats = external_api::clean_returnvalue(mod_chat_external::get_chats_by_courses_returns(), $chats);
        $this->assertCount(1, $chats['chats']);
        $this->assertEquals('First Chat', $chats['chats'][0]['name']);
                $this->assertCount(11, $chats['chats'][0]);

                $this->assertFalse(isset($chats['chats'][0]['section']));

                $chats = mod_chat_external::get_chats_by_courses(array($course2->id));
                $chats = external_api::clean_returnvalue(mod_chat_external::get_chats_by_courses_returns(), $chats);
        $this->assertCount(0, $chats['chats']);
        $this->assertEquals(1, $chats['warnings'][0]['warningcode']);

                $this->setAdminUser();
                $chats = mod_chat_external::get_chats_by_courses(array($course2->id));
                $chats = external_api::clean_returnvalue(mod_chat_external::get_chats_by_courses_returns(), $chats);

        $this->assertCount(1, $chats['chats']);
        $this->assertEquals('Second Chat', $chats['chats'][0]['name']);
                $this->assertCount(16, $chats['chats'][0]);
                $this->assertEquals(0, $chats['chats'][0]['section']);

                self::getDataGenerator()->enrol_user($student1->id,  $course2->id, $studentrole->id);
        $this->setUser($student1);
        $chats = mod_chat_external::get_chats_by_courses();
        $chats = external_api::clean_returnvalue(mod_chat_external::get_chats_by_courses_returns(), $chats);
        $this->assertCount(2, $chats['chats']);

    }
}
