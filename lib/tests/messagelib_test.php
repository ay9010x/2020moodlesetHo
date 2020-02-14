<?php



defined('MOODLE_INTERNAL') || die();

class core_messagelib_testcase extends advanced_testcase {

    public function test_message_provider_disabled() {
        $this->resetAfterTest();
        $this->preventResetByRollback();

                $disableprovidersetting = 'moodle_instantmessage_disable';
        set_config($disableprovidersetting, 1, 'message');
        $preferences = get_message_output_default_preferences();
        $this->assertTrue($preferences->$disableprovidersetting == 1);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = get_admin();
        $message->userto            = $this->getDataGenerator()->create_user();;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = 0;

                $sink = $this->redirectEmails();
        message_send($message);
        $emails = $sink->get_messages();
        $this->assertEmpty($emails);

                set_config($disableprovidersetting, 0, 'message');
        $preferences = get_message_output_default_preferences();
        $this->assertTrue($preferences->$disableprovidersetting == 0);

        $sink = $this->redirectEmails();
        message_send($message);
        $emails = $sink->get_messages();
        $email = reset($emails);
        $this->assertEquals($email->subject, 'message subject 1');
    }
    public function test_message_get_providers_for_user() {
        global $CFG, $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();

                $cat = $generator->create_category(array('parent' => 0));
        $course = $generator->create_course(array('category' => $cat->id));
        $quiz = $generator->create_module('quiz', array('course' => $course->id));
        $user = $generator->create_user();

        $coursecontext = context_course::instance($course->id);
        $quizcontext = context_module::instance($quiz->cmid);
        $frontpagecontext = context_course::instance(SITEID);

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                        role_assign($studentrole->id, $user->id, $coursecontext->id);
        assign_capability('mod/quiz:emailconfirmsubmission', CAP_ALLOW, $studentrole->id, $quizcontext->id);

                assign_capability('mod/quiz:emailwarnoverdue', CAP_ALLOW, $CFG->defaultfrontpageroleid, $frontpagecontext->id);

        $providers = message_get_providers_for_user($user->id);
        $this->assertTrue($this->message_type_present('mod_forum', 'posts', $providers));
        $this->assertTrue($this->message_type_present('mod_quiz', 'confirmation', $providers));
        $this->assertTrue($this->message_type_present('mod_quiz', 'attempt_overdue', $providers));
        $this->assertFalse($this->message_type_present('mod_quiz', 'submission', $providers));

                $course2 = $generator->create_course(array('category' => $cat->id));
        $user2 = $generator->create_user();
        $coursecontext2 = context_course::instance($course2->id);
        role_assign($studentrole->id, $user2->id, $coursecontext2->id);
        accesslib_clear_all_caches_for_unit_testing();
        $providers = message_get_providers_for_user($user2->id);
        $this->assertTrue($this->message_type_present('mod_forum', 'posts', $providers));
        $this->assertFalse($this->message_type_present('mod_quiz', 'confirmation', $providers));

                unset_config('defaultfrontpageroleid');
        accesslib_clear_all_caches_for_unit_testing();

        $providers = message_get_providers_for_user($user->id);
        $this->assertTrue($this->message_type_present('mod_quiz', 'confirmation', $providers));
        $this->assertFalse($this->message_type_present('mod_quiz', 'attempt_overdue', $providers));
        $this->assertFalse($this->message_type_present('mod_quiz', 'submission', $providers));
    }

    public function test_message_get_providers_for_user_more() {
        global $DB;

        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course();
        $coursecontext = context_course::instance($course->id);

                                $assign = $this->getDataGenerator()->create_module('assign', array('course'=>$course->id));
        $modulecontext = context_module::instance($assign->cmid);

                $teacherrole = $DB->get_record('role', array('shortname'=>'editingteacher'), '*', MUST_EXIST);
        $teacher = $this->getDataGenerator()->create_user();
        role_assign($teacherrole->id, $teacher->id, $coursecontext);
        $enrolplugin = enrol_get_plugin('manual');
        $enrolplugin->add_instance($course);
        $enrolinstances = enrol_get_instances($course->id, false);
        foreach ($enrolinstances as $enrolinstance) {
            if ($enrolinstance->enrol === 'manual') {
                break;
            }
        }
        $enrolplugin->enrol_user($enrolinstance, $teacher->id);

                $this->setUser($teacher);

                $this->assertFalse(has_capability('moodle/site:config', $modulecontext));
        $providers = message_get_providers_for_user($teacher->id);
        $this->assertFalse($this->message_type_present('moodle', 'backup', $providers));

                        assign_capability('moodle/site:config', CAP_ALLOW, $teacherrole->id, $modulecontext->id, true);
        accesslib_clear_all_caches_for_unit_testing();
        $modulecontext = context_module::instance($assign->cmid);
        $this->assertTrue(has_capability('moodle/site:config', $modulecontext));

        $providers = message_get_providers_for_user($teacher->id);
        $this->assertTrue($this->message_type_present('moodle', 'backup', $providers));

                                assign_capability('moodle/site:config', CAP_PROHIBIT, $teacherrole->id, $coursecontext->id, true);
        accesslib_clear_all_caches_for_unit_testing();
        $modulecontext = context_module::instance($assign->cmid);
        $this->assertFalse(has_capability('moodle/site:config', $modulecontext));

        $providers = message_get_providers_for_user($teacher->id);
                            }

    public function test_send_message_redirection() {
        global $DB;

        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

                $message = new stdClass();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $user1;
        $message->userto = $user2;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';

        $sink = $this->redirectMessages();
        $this->setCurrentTimeStart();
        $messageid = message_send($message);
        $savedmessages = $sink->get_messages();
        $this->assertCount(1, $savedmessages);
        $savedmessage = reset($savedmessages);
        $this->assertEquals($messageid, $savedmessage->id);
        $this->assertEquals($user1->id, $savedmessage->useridfrom);
        $this->assertEquals($user2->id, $savedmessage->useridto);
        $this->assertEquals($message->fullmessage, $savedmessage->fullmessage);
        $this->assertEquals($message->fullmessageformat, $savedmessage->fullmessageformat);
        $this->assertEquals($message->fullmessagehtml, $savedmessage->fullmessagehtml);
        $this->assertEquals($message->smallmessage, $savedmessage->smallmessage);
        $this->assertEquals($message->smallmessage, $savedmessage->smallmessage);
        $this->assertEquals($message->notification, $savedmessage->notification);
        $this->assertNull($savedmessage->contexturl);
        $this->assertNull($savedmessage->contexturlname);
        $this->assertTimeCurrent($savedmessage->timecreated);
        $record = $DB->get_record('message_read', array('id' => $savedmessage->id), '*', MUST_EXIST);
        $this->assertEquals($record, $savedmessage);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message', array()));
        $DB->delete_records('message_read', array());

        $message = new stdClass();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $user1->id;
        $message->userto = $user2->id;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';
        $message->contexturl = new moodle_url('/');
        $message->contexturlname = 'front';
        $sink = $this->redirectMessages();
        $messageid = message_send($message);
        $savedmessages = $sink->get_messages();
        $this->assertCount(1, $savedmessages);
        $savedmessage = reset($savedmessages);
        $this->assertEquals($messageid, $savedmessage->id);
        $this->assertEquals($user1->id, $savedmessage->useridfrom);
        $this->assertEquals($user2->id, $savedmessage->useridto);
        $this->assertEquals($message->fullmessage, $savedmessage->fullmessage);
        $this->assertEquals($message->fullmessageformat, $savedmessage->fullmessageformat);
        $this->assertEquals($message->fullmessagehtml, $savedmessage->fullmessagehtml);
        $this->assertEquals($message->smallmessage, $savedmessage->smallmessage);
        $this->assertEquals($message->smallmessage, $savedmessage->smallmessage);
        $this->assertEquals($message->notification, $savedmessage->notification);
        $this->assertEquals($message->contexturl->out(), $savedmessage->contexturl);
        $this->assertEquals($message->contexturlname, $savedmessage->contexturlname);
        $this->assertTimeCurrent($savedmessage->timecreated);
        $record = $DB->get_record('message_read', array('id' => $savedmessage->id), '*', MUST_EXIST);
        $this->assertEquals($record, $savedmessage);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message', array()));
        $DB->delete_records('message_read', array());

        
        $message = new stdClass();
        $message->component = 'xxxxx';
        $message->name = 'instantmessage';
        $message->userfrom = $user1;
        $message->userto = $user2;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';

        $sink = $this->redirectMessages();
        try {
            message_send($message);
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
        $this->assertCount(0, $sink->get_messages());

        $message->component = 'moodle';
        $message->name = 'xxx';
        $sink = $this->redirectMessages();
        try {
            message_send($message);
        } catch (moodle_exception $e) {
            $this->assertInstanceOf('coding_exception', $e);
        }
        $this->assertCount(0, $sink->get_messages());
        $sink->close();
        $this->assertFalse($DB->record_exists('message', array()));
        $this->assertFalse($DB->record_exists('message_read', array()));

        
        $message = new stdClass();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $user1;
        $message->userto = -1;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';

        $messageid = message_send($message);
        $this->assertFalse($messageid);
        $this->assertDebuggingCalled('Attempt to send msg to unknown user');

        $message = new stdClass();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = -1;
        $message->userto = $user2;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';

        $messageid = message_send($message);
        $this->assertFalse($messageid);
        $this->assertDebuggingCalled('Attempt to send msg from unknown user');

        $message = new stdClass();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $user1;
        $message->userto = core_user::NOREPLY_USER;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';

        $messageid = message_send($message);
        $this->assertFalse($messageid);
        $this->assertDebuggingCalled('Attempt to send msg to internal (noreply) user');

        
        unset($user2->emailstop);
        $message = new stdClass();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $user1;
        $message->userto = $user2;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';

        $sink = $this->redirectMessages();
        $messageid = message_send($message);
        $savedmessages = $sink->get_messages();
        $this->assertCount(1, $savedmessages);
        $savedmessage = reset($savedmessages);
        $this->assertEquals($messageid, $savedmessage->id);
        $this->assertEquals($user1->id, $savedmessage->useridfrom);
        $this->assertEquals($user2->id, $savedmessage->useridto);
        $this->assertDebuggingCalled('Necessary properties missing in userto object, fetching full record');
        $sink->clear();
        $user2->emailstop = '0';
    }

    public function test_send_message() {
        global $DB, $CFG;
        $this->preventResetByRollback();
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

                $this->assertFileExists("$CFG->dirroot/message/output/email/version.php");
        $this->assertFileExists("$CFG->dirroot/message/output/popup/version.php");

        $DB->set_field_select('message_processors', 'enabled', 0, "name <> 'email' AND name <> 'popup'");
        get_message_processors(true, true);

        $eventsink = $this->redirectEvents();

        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'none', $user2);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $sink = $this->redirectEmails();
        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(0, $emails);
        $savedmessage = $DB->get_record('message', array('id' => $messageid), '*', MUST_EXIST);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message_read', array()));
        $DB->delete_records('message', array());
        $events = $eventsink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $eventsink->clear();

        $CFG->messaging = 0;

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(0, $emails);
        $savedmessage = $DB->get_record('message_read', array('id' => $messageid), '*', MUST_EXIST);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message', array()));
        $DB->delete_records('message_read', array());
        $events = $eventsink->get_events();
        $this->assertCount(2, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $this->assertInstanceOf('\core\event\message_viewed', $events[1]);
        $eventsink->clear();

        $CFG->messaging = 1;

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '1';

        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(0, $emails);
        $savedmessage = $DB->get_record('message_read', array('id' => $messageid), '*', MUST_EXIST);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message', array()));
        $DB->delete_records('message_read', array());
        $events = $eventsink->get_events();
        $this->assertCount(2, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $this->assertInstanceOf('\core\event\message_viewed', $events[1]);
        $eventsink->clear();

        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'email', $user2);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $user2->emailstop = '1';

        $sink = $this->redirectEmails();
        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(0, $emails);
        $savedmessage = $DB->get_record('message', array('id' => $messageid), '*', MUST_EXIST);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message_read', array()));
        $DB->delete_records('message', array());
        $events = $eventsink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $eventsink->clear();
        $user2->emailstop = '0';

        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'email', $user2);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(1, $emails);
        $email = reset($emails);
        $savedmessage = $DB->get_record('message_read', array('id' => $messageid), '*', MUST_EXIST);
        $this->assertSame($user1->email, $email->from);
        $this->assertSame($user2->email, $email->to);
        $this->assertSame($message->subject, $email->subject);
        $this->assertNotEmpty($email->header);
        $this->assertNotEmpty($email->body);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message', array()));
        $DB->delete_records('message_read', array());
        $events = $eventsink->get_events();
        $this->assertCount(2, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $this->assertInstanceOf('\core\event\message_viewed', $events[1]);
        $eventsink->clear();

        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'email,popup', $user2);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(1, $emails);
        $email = reset($emails);
        $savedmessage = $DB->get_record('message', array('id' => $messageid), '*', MUST_EXIST);
        $working = $DB->get_record('message_working', array('unreadmessageid' => $messageid), '*', MUST_EXIST);
        $this->assertSame($user1->email, $email->from);
        $this->assertSame($user2->email, $email->to);
        $this->assertSame($message->subject, $email->subject);
        $this->assertNotEmpty($email->header);
        $this->assertNotEmpty($email->body);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message_read', array()));
        $DB->delete_records('message', array());
        $events = $eventsink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $eventsink->clear();

        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'popup', $user2);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(0, $emails);
        $savedmessage = $DB->get_record('message', array('id' => $messageid), '*', MUST_EXIST);
        $working = $DB->get_record('message_working', array('unreadmessageid' => $messageid), '*', MUST_EXIST);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message_read', array()));
        $DB->delete_records('message', array());
        $events = $eventsink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $eventsink->clear();

        $this->assertFalse($DB->is_transaction_started());
        $transaction = $DB->start_delegated_transaction();
        if (!$DB->is_transaction_started()) {
            $this->markTestSkipped('Databases that do not support transactions should not be used at all!');
        }
        $transaction->allow_commit();

        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'none', $user2);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $transaction = $DB->start_delegated_transaction();
        $sink = $this->redirectEmails();
        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(0, $emails);
        $savedmessage = $DB->get_record('message', array('id' => $messageid), '*', MUST_EXIST);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message_read', array()));
        $DB->delete_records('message', array());
        $events = $eventsink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $eventsink->clear();
        $transaction->allow_commit();
        $events = $eventsink->get_events();
        $this->assertCount(0, $events);

        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'email', $user2);

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $transaction = $DB->start_delegated_transaction();
        $sink = $this->redirectEmails();
        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(0, $emails);
        $savedmessage = $DB->get_record('message', array('id' => $messageid), '*', MUST_EXIST);
        $sink->clear();
        $this->assertFalse($DB->record_exists('message_read', array()));
        $events = $eventsink->get_events();
        $this->assertCount(0, $events);
        $transaction->allow_commit();
        $events = $eventsink->get_events();
        $this->assertCount(2, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $this->assertInstanceOf('\core\event\message_viewed', $events[1]);
        $eventsink->clear();

        $transaction = $DB->start_delegated_transaction();
        message_send($message);
        message_send($message);
        $this->assertCount(2, $DB->get_records('message'));
        $this->assertCount(1, $DB->get_records('message_read'));
        $events = $eventsink->get_events();
        $this->assertCount(0, $events);
        $transaction->allow_commit();
        $events = $eventsink->get_events();
        $this->assertCount(4, $events);
        $this->assertInstanceOf('\core\event\message_sent', $events[0]);
        $this->assertInstanceOf('\core\event\message_viewed', $events[1]);
        $this->assertInstanceOf('\core\event\message_sent', $events[2]);
        $this->assertInstanceOf('\core\event\message_viewed', $events[3]);
        $eventsink->clear();
        $DB->delete_records('message', array());
        $DB->delete_records('message_read', array());

        $transaction = $DB->start_delegated_transaction();
        message_send($message);
        message_send($message);
        $this->assertCount(2, $DB->get_records('message'));
        $this->assertCount(0, $DB->get_records('message_read'));
        $events = $eventsink->get_events();
        $this->assertCount(0, $events);
        try {
            $transaction->rollback(new Exception('ignore'));
        } catch (Exception $e) {
            $this->assertSame('ignore', $e->getMessage());
        }
        $events = $eventsink->get_events();
        $this->assertCount(0, $events);
        $this->assertCount(0, $DB->get_records('message'));
        $this->assertCount(0, $DB->get_records('message_read'));
        message_send($message);
        $this->assertCount(0, $DB->get_records('message'));
        $this->assertCount(1, $DB->get_records('message_read'));
        $events = $eventsink->get_events();
        $this->assertCount(2, $events);
        $sink->clear();
        $DB->delete_records('message_read', array());
    }

    public function test_rollback() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        $mailsink = $this->redirectEmails();

                message_send($message);
        $this->assertEquals(1, $mailsink->count());

        $transaction1 = $DB->start_delegated_transaction();

        $mailsink->clear();
        message_send($message);
        $this->assertEquals(0, $mailsink->count());

        $transaction2 = $DB->start_delegated_transaction();

        $mailsink->clear();
        message_send($message);
        $this->assertEquals(0, $mailsink->count());

        try {
            $transaction2->rollback(new Exception('x'));
            $this->fail('Expecting exception');
        } catch (Exception $e) {}
        $this->assertDebuggingNotCalled();
        $this->assertEquals(0, $mailsink->count());

        $this->assertTrue($DB->is_transaction_started());

        try {
            $transaction1->rollback(new Exception('x'));
            $this->fail('Expecting exception');
        } catch (Exception $e) {}
        $this->assertDebuggingNotCalled();
        $this->assertEquals(0, $mailsink->count());

        $this->assertFalse($DB->is_transaction_started());

        message_send($message);
        $this->assertEquals(1, $mailsink->count());
    }

    public function test_forced_rollback() {
        global $DB;

        $this->resetAfterTest();
        $this->preventResetByRollback();
        set_config('noemailever', 1);

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = $user1;
        $message->userto            = $user2;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->notification      = '0';

        message_send($message);
        $this->assertDebuggingCalled('Not sending email due to $CFG->noemailever config setting');

        $transaction1 = $DB->start_delegated_transaction();

        message_send($message);
        $this->assertDebuggingNotCalled();

        $transaction2 = $DB->start_delegated_transaction();

        message_send($message);
        $this->assertDebuggingNotCalled();

        $DB->force_transaction_rollback();
        $this->assertFalse($DB->is_transaction_started());
        $this->assertDebuggingNotCalled();

        message_send($message);
        $this->assertDebuggingCalled('Not sending email due to $CFG->noemailever config setting');
    }

    public function test_message_attachment_send() {
        global $CFG;
        $this->preventResetByRollback();
        $this->resetAfterTest();

                $CFG->allowattachments = true;
        unset_config('noemailever');

        $user = $this->getDataGenerator()->create_user();
        $context = context_user::instance($user->id);

                $fs = get_file_storage();
        $filerecord = array(
                'contextid' => $context->id,
                'component' => 'core',
                'filearea'  => 'unittest',
                'itemid'    => 99999,
                'filepath'  => '/',
                'filename'  => 'emailtest.txt'
        );
        $file = $fs->create_file_from_string($filerecord, 'Test content');

        $message = new stdClass();
        $message->component         = 'moodle';
        $message->name              = 'instantmessage';
        $message->userfrom          = get_admin();
        $message->userto            = $user;
        $message->subject           = 'message subject 1';
        $message->fullmessage       = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml   = '<p>message body</p>';
        $message->smallmessage      = 'small message';
        $message->attachment        = $file;
        $message->attachname        = 'emailtest.txt';
        $message->notification      = 0;

                $sink = $this->redirectEmails();
        message_send($message);

                $emails = $sink->get_messages();
        $email = reset($emails);
        $this->assertTrue(strpos($email->body, 'Content-Disposition: attachment;') !== false);
        $this->assertTrue(strpos($email->body, 'emailtest.txt') !== false);

                $storedfileexists = $fs->file_exists($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'],
                                             $filerecord['itemid'], $filerecord['filepath'], $filerecord['filename']);
        $this->assertTrue($storedfileexists);
    }

    
    protected function message_type_present($component, $name, $providers) {
        foreach ($providers as $provider) {
            if ($provider->component == $component && $provider->name == $name) {
                return true;
            }
        }
        return false;
    }
}
