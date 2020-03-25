<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class core_message_testcase extends advanced_testcase {

    
    public function test_get_eventobject_for_processor() {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();

        $message = new \core\message\message();
        $message->component = 'moodle';
        $message->name = 'instantmessage';
        $message->userfrom = $USER;
        $message->userto = $user;
        $message->subject = 'message subject 1';
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>message body</p>';
        $message->smallmessage = 'small message';
        $message->notification = '0';
        $message->contexturl = 'http://GalaxyFarFarAway.com';
        $message->contexturlname = 'Context name';
        $message->replyto = "random@example.com";
        $message->attachname = 'attachment';
        $content = array('*' => array('header' => ' test ', 'footer' => ' test '));         $message->set_additional_content('test', $content);

                $usercontext = context_user::instance($user->id);
        $file = new stdClass;
        $file->contextid = $usercontext->id;
        $file->component = 'user';
        $file->filearea  = 'private';
        $file->itemid    = 0;
        $file->filepath  = '/';
        $file->filename  = '1.txt';
        $file->source    = 'test';

        $fs = get_file_storage();
        $file = $fs->create_file_from_string($file, 'file1 content');
        $message->attachment = $file;

        $stdclass = $message->get_eventobject_for_processor('test');

        $this->assertSame($message->component, $stdclass->component);
        $this->assertSame($message->name, $stdclass->name);
        $this->assertSame($message->userfrom, $stdclass->userfrom);
        $this->assertSame($message->userto, $stdclass->userto);
        $this->assertSame($message->subject, $stdclass->subject);
        $this->assertSame(' test ' . $message->fullmessage . ' test ', $stdclass->fullmessage);
        $this->assertSame(' test ' . $message->fullmessagehtml . ' test ', $stdclass->fullmessagehtml);
        $this->assertSame(' test ' . $message->smallmessage . ' test ', $stdclass->smallmessage);
        $this->assertSame($message->notification, $stdclass->notification);
        $this->assertSame($message->contexturl, $stdclass->contexturl);
        $this->assertSame($message->contexturlname, $stdclass->contexturlname);
        $this->assertSame($message->replyto, $stdclass->replyto);
        $this->assertSame($message->attachname, $stdclass->attachname);

                $content = array('fullmessage' => array('header' => ' test ', 'footer' => ' test '));
        $message->set_additional_content('test', $content);
        $stdclass = $message->get_eventobject_for_processor('test');
        $this->assertSame(' test ' . $message->fullmessage . ' test ', $stdclass->fullmessage);
        $this->assertSame($message->fullmessagehtml, $stdclass->fullmessagehtml);
        $this->assertSame($message->smallmessage, $stdclass->smallmessage);

                $content = array('fullmessagehtml' => array('header' => ' test ', 'footer' => ' test '),
                         'smallmessage' => array('header' => ' testsmall ', 'footer' => ' testsmall '));
        $message->set_additional_content('test', $content);
        $stdclass = $message->get_eventobject_for_processor('test');
        $this->assertSame($message->fullmessage, $stdclass->fullmessage);
        $this->assertSame(' test ' . $message->fullmessagehtml . ' test ', $stdclass->fullmessagehtml);
        $this->assertSame(' testsmall ' . $message->smallmessage . ' testsmall ', $stdclass->smallmessage);

                $content = array('*' => array('header' => ' test ', 'footer' => ' test '),
                         'smallmessage' => array('header' => ' testsmall ', 'footer' => ' testsmall '));
        $message->set_additional_content('test', $content);
        $stdclass = $message->get_eventobject_for_processor('test');
        $this->assertSame(' test ' . $message->fullmessage . ' test ', $stdclass->fullmessage);
        $this->assertSame(' test ' . $message->fullmessagehtml . ' test ', $stdclass->fullmessagehtml);
        $this->assertSame(' testsmall ' . ' test ' .  $message->smallmessage . ' test ' . ' testsmall ', $stdclass->smallmessage);
    }

    
    public function test_send_message() {
        global $DB, $CFG;
        $this->preventResetByRollback();
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

                $this->assertFileExists("$CFG->dirroot/message/output/email/version.php");
        $this->assertFileExists("$CFG->dirroot/message/output/popup/version.php");

        $DB->set_field_select('message_processors', 'enabled', 0, "name <> 'email'");
        set_user_preference('message_provider_moodle_instantmessage_loggedoff', 'email', $user2);

                $message = new \core\message\message();
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
        $content = array('*' => array('header' => ' test ', 'footer' => ' test '));
        $message->set_additional_content('email', $content);

        $sink = $this->redirectEmails();
        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(1, $emails);
        $email = reset($emails);
        $recordexists = $DB->record_exists('message_read', array('id' => $messageid));
        $this->assertSame(true, $recordexists);
        $this->assertSame($user1->email, $email->from);
        $this->assertSame($user2->email, $email->to);
        $this->assertSame($message->subject, $email->subject);
        $this->assertNotEmpty($email->header);
        $this->assertNotEmpty($email->body);
        $this->assertRegExp('/test message body test/', $email->body);
        $sink->clear();

                        $message = new \core\message\message();
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
        $content = array('smallmessage' => array('header' => ' test ', 'footer' => ' test '));
        $message->set_additional_content('email', $content);

        $messageid = message_send($message);
        $emails = $sink->get_messages();
        $this->assertCount(1, $emails);
        $email = reset($emails);
        $recordexists = $DB->record_exists('message_read', array('id' => $messageid));
        $this->assertSame(true, $recordexists);
        $this->assertSame($user1->email, $email->from);
        $this->assertSame($user2->email, $email->to);
        $this->assertSame($message->subject, $email->subject);
        $this->assertNotEmpty($email->header);
        $this->assertNotEmpty($email->body);
        $this->assertNotRegExp('/test message body test/', $email->body);
        $sink->close();
    }
}
