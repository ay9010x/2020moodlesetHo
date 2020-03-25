<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;

class mod_forum_mail_testcase extends advanced_testcase {

    protected $helper;

    public function setUp() {
                        \mod_forum\subscriptions::reset_forum_cache();
        \mod_forum\subscriptions::reset_discussion_cache();

        global $CFG;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

        $helper = new stdClass();

                $this->preventResetByRollback();

                $helper->messagesink = $this->redirectMessages();
        $helper->mailsink = $this->redirectEmails();

                $messages = $helper->messagesink->get_messages();
        $this->assertEquals(0, count($messages));

        $messages = $helper->mailsink->get_messages();
        $this->assertEquals(0, count($messages));

                        $CFG->maxeditingtime = -1;

        $this->helper = $helper;
    }

    public function tearDown() {
                        \mod_forum\subscriptions::reset_forum_cache();

        $this->helper->messagesink->clear();
        $this->helper->messagesink->close();

        $this->helper->mailsink->clear();
        $this->helper->mailsink->close();
    }

    
    protected function helper_spoof_message_inbound_setup() {
        global $CFG, $DB;
                $CFG->messageinbound_domain = 'example.com';
        $CFG->messageinbound_enabled = true;

                $CFG->messageinbound_mailbox = 'moodlemoodle123';

        $record = $DB->get_record('messageinbound_handlers', array('classname' => '\mod_forum\message\inbound\reply_handler'));
        $record->enabled = true;
        $record->id = $DB->update_record('messageinbound_handlers', $record);
    }

    
    protected function helper_create_users($course, $count) {
        $users = array();

        for ($i = 0; $i < $count; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
            $users[] = $user;
        }

        return $users;
    }

    
    protected function helper_post_to_forum($forum, $author, $fields = array()) {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_forum');

                $record = (object)$fields;
        $record->course = $forum->course;
        $record->userid = $author->id;
        $record->forum = $forum->id;
        $discussion = $generator->create_discussion($record);

                $post = $DB->get_record('forum_posts', array('discussion' => $discussion->id));

        return array($discussion, $post);
    }

    
    protected function helper_update_post_time($post, $factor) {
        global $DB;

                $DB->set_field('forum_posts', 'created', $post->created + $factor, array('id' => $post->id));
    }

    
    protected function helper_update_subscription_time($user, $discussion, $factor) {
        global $DB;

        $sub = $DB->get_record('forum_discussion_subs', array('userid' => $user->id, 'discussion' => $discussion->id));

                $DB->set_field('forum_discussion_subs', 'preference', $sub->preference + $factor, array('id' => $sub->id));
    }

    
    protected function helper_post_to_discussion($forum, $discussion, $author) {
        global $DB;

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_forum');

                $record = new stdClass();
        $record->course = $forum->course;
        $strre = get_string('re', 'forum');
        $record->subject = $strre . ' ' . $discussion->subject;
        $record->userid = $author->id;
        $record->forum = $forum->id;
        $record->discussion = $discussion->id;
        $record->mailnow = 1;

        $post = $generator->create_post($record);

        return $post;
    }

    
    protected function helper_run_cron_check_count($post, $expected) {

                $this->helper->messagesink->clear();
        $this->helper->mailsink->clear();

                $this->expectOutputRegex("/{$expected} users were sent post {$post->id}, '{$post->subject}'/");
        forum_cron();

                $messages = $this->helper->messagesink->get_messages();

                $this->assertEquals($expected, count($messages));

        return $messages;
    }

    
    protected function helper_run_cron_check_counts($posts, $expected) {

                $this->helper->messagesink->clear();
        $this->helper->mailsink->clear();

                foreach ($posts as $post) {
            $this->expectOutputRegex("/{$post['count']} users were sent post {$post['id']}, '{$post['subject']}'/");
        }
        forum_cron();

                $messages = $this->helper->messagesink->get_messages();

                $this->assertEquals($expected, count($messages));

        return $messages;
    }

    public function test_forced_subscription() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $expected = 2;

                $messages = $this->helper_run_cron_check_count($post, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertTrue($seenauthor);
        $this->assertTrue($seenrecipient);
    }

    public function test_subscription_disabled() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_DISALLOWSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $expected = 0;

                $messages = $this->helper_run_cron_check_count($post, $expected);

                $expected = 1;
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        assign_capability('moodle/course:manageactivities', CAP_ALLOW, $roleids['student'], context_course::instance($course->id));
        \mod_forum\subscriptions::subscribe_user($recipient->id, $forum);

        $this->assertEquals($expected, $DB->count_records('forum_subscriptions', array(
            'userid'        => $recipient->id,
            'forum'         => $forum->id,
        )));

                list($discussion, $post) = $this->helper_post_to_forum($forum, $recipient);
        $messages = $this->helper_run_cron_check_count($post, $expected);

                \mod_forum\subscriptions::unsubscribe_user($recipient->id, $forum);

        $expected = 0;
        $this->assertEquals($expected, $DB->count_records('forum_subscriptions', array(
            'userid'        => $recipient->id,
            'forum'         => $forum->id,
        )));

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
        $messages = $this->helper_run_cron_check_count($post, $expected);

                \mod_forum\subscriptions::subscribe_user_to_discussion($recipient->id, $discussion);
        $this->helper_update_subscription_time($recipient, $discussion, -60);

        $reply = $this->helper_post_to_discussion($forum, $discussion, $author);
        $this->helper_update_post_time($reply, -30);

        $messages = $this->helper_run_cron_check_count($reply, $expected);
    }

    public function test_automatic() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $expected = 2;

                $messages = $this->helper_run_cron_check_count($post, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertTrue($seenauthor);
        $this->assertTrue($seenrecipient);
    }

    public function test_optional() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $expected = 0;

                $messages = $this->helper_run_cron_check_count($post, $expected);
    }

    public function test_automatic_with_unsubscribed_user() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                \mod_forum\subscriptions::unsubscribe_user($author->id, $forum);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $expected = 1;

                $messages = $this->helper_run_cron_check_count($post, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertFalse($seenauthor);
        $this->assertTrue($seenrecipient);
    }

    public function test_optional_with_subscribed_user() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                \mod_forum\subscriptions::subscribe_user($recipient->id, $forum);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $expected = 1;

                $messages = $this->helper_run_cron_check_count($post, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertFalse($seenauthor);
        $this->assertTrue($seenrecipient);
    }

    public function test_automatic_with_unsubscribed_discussion() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

        $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));
        $this->assertTrue(\mod_forum\subscriptions::is_subscribed($recipient->id, $forum, $discussion->id));

                $expected = 1;

                $messages = $this->helper_run_cron_check_count($post, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertFalse($seenauthor);
        $this->assertTrue($seenrecipient);
    }

    public function test_optional_with_subscribed_discussion() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
        $this->helper_update_post_time($post, -90);

                \mod_forum\subscriptions::subscribe_user_to_discussion($recipient->id, $discussion);
        $this->helper_update_subscription_time($recipient, $discussion, -60);

                        $expected = 0;

                $messages = $this->helper_run_cron_check_count($post, $expected);

                $reply = $this->helper_post_to_discussion($forum, $discussion, $author);
        $this->helper_update_post_time($reply, -30);

                $expected = 1;

                $messages = $this->helper_run_cron_check_count($reply, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertFalse($seenauthor);
        $this->assertTrue($seenrecipient);
    }

    public function test_automatic_with_subscribed_discussion_in_unsubscribed_forum() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
        $this->helper_update_post_time($post, -90);

                \mod_forum\subscriptions::unsubscribe_user($author->id, $forum);

                \mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);
        $this->helper_update_subscription_time($author, $discussion, -60);

                        $expected = 1;

                $messages = $this->helper_run_cron_check_count($post, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertFalse($seenauthor);
        $this->assertTrue($seenrecipient);

                $reply = $this->helper_post_to_discussion($forum, $discussion, $author);
        $this->helper_update_post_time($reply, -30);

                $expected = 2;

                $messages = $this->helper_run_cron_check_count($reply, $expected);

        $seenauthor = false;
        $seenrecipient = false;
        foreach ($messages as $message) {
                        $this->assertEquals($author->id, $message->useridfrom);

            if ($message->useridto == $author->id) {
                $seenauthor = true;
            } else if ($message->useridto = $recipient->id) {
                $seenrecipient = true;
            }
        }

                $this->assertTrue($seenauthor);
        $this->assertTrue($seenrecipient);
    }

    public function test_optional_with_unsubscribed_discussion_in_subscribed_forum() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author, $recipient) = $this->helper_create_users($course, 2);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                \mod_forum\subscriptions::subscribe_user($recipient->id, $forum);

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($recipient->id, $discussion);

                $expected = 0;

                $messages = $this->helper_run_cron_check_count($post, $expected);
    }

    
    public function test_forum_discussion_subscription_forum_unsubscribed_discussion_subscribed_after_post() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

        $expectedmessages = array();

                list($author) = $this->helper_create_users($course, 1);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
        $this->helper_update_post_time($post, -90);

        $expectedmessages[] = array(
            'id' => $post->id,
            'subject' => $post->subject,
            'count' => 0,
        );

                $this->assertTrue(\mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));
        $this->helper_update_subscription_time($author, $discussion, -60);

                $reply = $this->helper_post_to_discussion($forum, $discussion, $author);
        $this->helper_update_post_time($reply, -30);

        $expectedmessages[] = array(
            'id' => $reply->id,
            'subject' => $reply->subject,
            'count' => 1,
        );

        $expectedcount = 1;

                $messages = $this->helper_run_cron_check_counts($expectedmessages, $expectedcount);
    }

    public function test_forum_message_inbound_multiple_posts() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 1);

        $expectedmessages = array();

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
        $this->helper_update_post_time($post, -90);

        $expectedmessages[] = array(
            'id' => $post->id,
            'subject' => $post->subject,
            'count' => 0,
        );

                $reply = $this->helper_post_to_discussion($forum, $discussion, $author);
        $this->helper_update_post_time($reply, -60);

        $expectedmessages[] = array(
            'id' => $reply->id,
            'subject' => $reply->subject,
            'count' => 1,
        );

        $expectedcount = 2;

                $this->helper_spoof_message_inbound_setup();

        $author->emailstop = '0';
        set_user_preference('message_provider_mod_forum_posts_loggedoff', 'email', $author);
        set_user_preference('message_provider_mod_forum_posts_loggedin', 'email', $author);

                        $this->helper->mailsink->clear();
        $this->helper->messagesink->close();

                foreach ($expectedmessages as $post) {
            $this->expectOutputRegex("/{$post['count']} users were sent post {$post['id']}, '{$post['subject']}'/");
        }

        forum_cron();
        $messages = $this->helper->mailsink->get_messages();

                $this->assertEquals($expectedcount, count($messages));

        foreach ($messages as $message) {
            $this->assertRegExp('/Reply-To: moodlemoodle123\+[^@]*@example.com/', $message->header);
        }
    }

    public function test_long_subject() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 1);

                $subject = 'This is the very long forum post subject that somebody was very kind of leaving, it is intended to check if long subject comes in mail correctly. Thank you.';
        $a = (object)array('courseshortname' => $course->shortname, 'forumname' => $forum->name, 'subject' => $subject);
        $expectedsubject = get_string('postmailsubject', 'forum', $a);
        list($discussion, $post) = $this->helper_post_to_forum($forum, $author, array('name' => $subject));

                $messages = $this->helper_run_cron_check_count($post, 1);
        $message = reset($messages);
        $this->assertEquals($author->id, $message->useridfrom);
        $this->assertEquals($expectedsubject, $message->subject);
    }

    
    public function test_subjects() {
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

        list($author) = $this->helper_create_users($course, 1);
        list($commenter) = $this->helper_create_users($course, 1);

        $strre = get_string('re', 'forum');

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
        $messages = $this->helper_run_cron_check_count($post, 2);
        $this->assertNotContains($strre, $messages[0]->subject);

                $reply = $this->helper_post_to_discussion($forum, $discussion, $commenter);
        $messages = $this->helper_run_cron_check_count($reply, 2);
        $this->assertContains($strre, $messages[0]->subject);
    }

    
    public function forum_post_email_templates_provider() {
                $base = array(
            'user' => array('firstname' => 'Love', 'lastname' => 'Moodle', 'mailformat' => 0, 'maildigest' => 0),
            'course' => array('shortname' => '101', 'fullname' => 'Moodle 101'),
            'forums' => array(
                array(
                    'name' => 'Moodle Forum',
                    'forumposts' => array(
                        array(
                            'name' => 'Hello Moodle',
                            'message' => 'Welcome to Moodle',
                            'messageformat' => FORMAT_MOODLE,
                            'attachments' => array(
                                array(
                                    'filename' => 'example.txt',
                                    'filecontents' => 'Basic information about the course'
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'expectations' => array(
                array(
                    'subject' => '.*101.*Hello',
                    'contents' => array(
                        '~{$a',
                        '~&(amp|lt|gt|quot|\#039);(?!course)',
                        'Attachment example.txt:\n' .
                            'http://www.example.com/moodle/pluginfile.php/\d*/mod_forum/attachment/\d*/example.txt\n',
                        'Hello Moodle', 'Moodle Forum', 'Welcome.*Moodle', 'Love Moodle', '1\d1'
                    ),
                ),
            ),
        );

                $textcases = array('Text mail without ampersands, quotes or lt/gt' => array('data' => $base));

                $newcase = $base;
        $newcase['user']['lastname'] = 'Moodle\'"';
        $newcase['course']['shortname'] = '101\'"';
        $newcase['forums'][0]['name'] = 'Moodle Forum\'"';
        $newcase['forums'][0]['forumposts'][0]['name'] = 'Hello Moodle\'"';
        $newcase['forums'][0]['forumposts'][0]['message'] = 'Welcome to Moodle\'"';
        $newcase['expectations'][0]['contents'] = array(
            'Attachment example.txt:', '~{\$a', '~&amp;(quot|\#039);', 'Love Moodle\'', '101\'', 'Moodle Forum\'"',
            'Hello Moodle\'"', 'Welcome to Moodle\'"');
        $textcases['Text mail with quotes everywhere'] = array('data' => $newcase);

                                $newcase = $base;
        $newcase['user']['lastname'] = 'Moodle>';
        $newcase['course']['shortname'] = '101>';
        $newcase['forums'][0]['name'] = 'Moodle Forum>';
        $newcase['forums'][0]['forumposts'][0]['name'] = 'Hello Moodle>';
        $newcase['forums'][0]['forumposts'][0]['message'] = 'Welcome to Moodle>';
        $newcase['expectations'][0]['contents'] = array(
            'Attachment example.txt:', '~{\$a', '~&amp;gt;', 'Love Moodle>', '101>', 'Moodle Forum>',
            'Hello Moodle>', 'Welcome to Moodle>');
        $textcases['Text mail with gt and lt everywhere'] = array('data' => $newcase);

                        $newcase = $base;
        $newcase['user']['lastname'] = 'Moodle&';
        $newcase['course']['shortname'] = '101&';
        $newcase['forums'][0]['name'] = 'Moodle Forum&';
        $newcase['forums'][0]['forumposts'][0]['name'] = 'Hello Moodle&';
        $newcase['forums'][0]['forumposts'][0]['message'] = 'Welcome to Moodle&';
        $newcase['expectations'][0]['contents'] = array(
            'Attachment example.txt:', '~{\$a', '~&amp;amp;', 'Love Moodle&', '101&', 'Moodle Forum&',
            'Hello Moodle&', 'Welcome to Moodle&');
        $textcases['Text mail with ampersands everywhere'] = array('data' => $newcase);

                $newcase = $base;
        $newcase['forums'][0]['forumposts'][0]['name'] = 'Text and image';
        $newcase['forums'][0]['forumposts'][0]['message'] = 'Welcome to Moodle, '
            .'@@PLUGINFILE@@/Screen%20Shot%202016-03-22%20at%205.54.36%20AM%20%281%29.png !';
        $newcase['expectations'][0]['subject'] = '.*101.*Text and image';
        $newcase['expectations'][0]['contents'] = array(
            '~{$a',
            '~&(amp|lt|gt|quot|\#039);(?!course)',
            'Attachment example.txt:\n' .
            'http://www.example.com/moodle/pluginfile.php/\d*/mod_forum/attachment/\d*/example.txt\n',
            'Text and image', 'Moodle Forum',
            'Welcome to Moodle, *\n.*'
                .'http://www.example.com/moodle/pluginfile.php/\d+/mod_forum/post/\d+/'
                .'Screen%20Shot%202016-03-22%20at%205\.54\.36%20AM%20%281%29\.png *\n.*!',
            'Love Moodle', '1\d1');
        $textcases['Text mail with text+image message i.e. @@PLUGINFILE@@ token handling'] = array('data' => $newcase);

                $htmlcases = array();

                $htmlbase = $base;
        $htmlbase['user']['mailformat'] = 1;
        $htmlbase['expectations'][0]['contents'] = array(
            '~{\$a',
            '~&(amp|lt|gt|quot|\#039);(?!course)',
            '<div class="attachments">( *\n *)?<a href',
            '<div class="subject">\n.*Hello Moodle', '>Moodle Forum', '>Welcome.*Moodle', '>Love Moodle', '>1\d1');
        $htmlcases['HTML mail without ampersands, quotes or lt/gt'] = array('data' => $htmlbase);

                $newcase = $htmlbase;
        $newcase['user']['lastname'] = 'Moodle\'">&';
        $newcase['course']['shortname'] = '101\'">&';
        $newcase['forums'][0]['name'] = 'Moodle Forum\'">&';
        $newcase['forums'][0]['forumposts'][0]['name'] = 'Hello Moodle\'">&';
        $newcase['forums'][0]['forumposts'][0]['message'] = 'Welcome to Moodle\'">&';
        $newcase['expectations'][0]['contents'] = array(
            '~{\$a',
            '~&amp;(amp|lt|gt|quot|\#039);',
            '<div class="attachments">( *\n *)?<a href',
            '<div class="subject">\n.*Hello Moodle\'"&gt;&amp;', '>Moodle Forum\'"&gt;&amp;',
            '>Welcome.*Moodle\'"&gt;&amp;', '>Love Moodle&\#039;&quot;&gt;&amp;', '>101\'"&gt;&amp');
        $htmlcases['HTML mail with quotes, gt, lt and ampersand  everywhere'] = array('data' => $newcase);

                $newcase = $htmlbase;
        $newcase['forums'][0]['forumposts'][0]['name'] = 'HTML text and image';
        $newcase['forums'][0]['forumposts'][0]['message'] = '<p>Welcome to Moodle, '
            .'<img src="@@PLUGINFILE@@/Screen%20Shot%202016-03-22%20at%205.54.36%20AM%20%281%29.png"'
            .' alt="" width="200" height="393" class="img-responsive" />!</p>';
        $newcase['expectations'][0]['subject'] = '.*101.*HTML text and image';
        $newcase['expectations'][0]['contents'] = array(
            '~{\$a',
            '~&(amp|lt|gt|quot|\#039);(?!course)',
            '<div class="attachments">( *\n *)?<a href',
            '<div class="subject">\n.*HTML text and image', '>Moodle Forum',
            '<p>Welcome to Moodle, '
                .'<img src="http://www.example.com/moodle/pluginfile.php/\d+/mod_forum/post/\d+/'
                .'Screen%20Shot%202016-03-22%20at%205\.54\.36%20AM%20%281%29\.png"'
                .' alt="" width="200" height="393" class="img-responsive" />!</p>',
            '>Love Moodle', '>1\d1');
        $htmlcases['HTML mail with text+image message i.e. @@PLUGINFILE@@ token handling'] = array('data' => $newcase);

        return $textcases + $htmlcases;
    }

    
    public function test_forum_post_email_templates($data) {
        global $DB;

        $this->resetAfterTest();

                $options = array();
        foreach ($data['course'] as $option => $value) {
            $options[$option] = $value;
        }
        $course = $this->getDataGenerator()->create_course($options);

                $options = array();
        foreach ($data['user'] as $option => $value) {
            $options[$option] = $value;
        }
        $user = $this->getDataGenerator()->create_user($options);
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

                $posts = array();
        foreach ($data['forums'] as $dataforum) {
            $forumposts = isset($dataforum['forumposts']) ? $dataforum['forumposts'] : array();
            unset($dataforum['forumposts']);
            $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
            foreach ($dataforum as $option => $value) {
                $options[$option] = $value;
            }
            $forum = $this->getDataGenerator()->create_module('forum', $options);

                        foreach ($forumposts as $forumpost) {
                $attachments = isset($forumpost['attachments']) ? $forumpost['attachments'] : array();
                unset($forumpost['attachments']);
                $postoptions = array('course' => $course->id, 'forum' => $forum->id, 'userid' => $user->id,
                    'mailnow' => 1, 'attachment' => !empty($attachments));
                foreach ($forumpost as $option => $value) {
                    $postoptions[$option] = $value;
                }
                list($discussion, $post) = $this->helper_post_to_forum($forum, $user, $postoptions);
                $posts[$post->subject] = $post; 
                                if ($attachments) {
                    $fs = get_file_storage();
                    foreach ($attachments as $attachment) {
                        $filerecord = array(
                            'contextid' => context_module::instance($forum->cmid)->id,
                            'component' => 'mod_forum',
                            'filearea'  => 'attachment',
                            'itemid'    => $post->id,
                            'filepath'  => '/',
                            'filename'  => $attachment['filename']
                        );
                        $fs->create_file_from_string($filerecord, $attachment['filecontents']);
                    }
                    $DB->set_field('forum_posts', 'attachment', '1', array('id' => $post->id));
                }
            }
        }

                        $this->helper->mailsink->clear();
        $this->helper->messagesink->close();

                foreach ($posts as $post) {
            $this->expectOutputRegex("/1 users were sent post {$post->id}, '{$post->subject}'/");
        }
        forum_cron(); 
                $mails = $this->helper->mailsink->get_messages();

                $expectations = $data['expectations'];

                $this->assertSame(count($expectations), count($mails));

                foreach ($mails as $mail) {
                        $foundexpectation = null;
            foreach ($expectations as $key => $expectation) {
                                if (!isset($expectation['subject'])) {
                    $this->fail('Provider expectation missing mandatory subject');
                }
                if (preg_match('!' . $expectation['subject'] . '!', $mail->subject)) {
                                        if (isset($foundexpectation)) {
                        $this->fail('Multiple expectations found (by subject matching). Please make them unique.');
                    }
                    $foundexpectation = $expectation;
                    unset($expectations[$key]);
                }
            }
                        $this->assertNotEmpty($foundexpectation, 'Expectation not found for the mail');

                        if (isset($foundexpectation) and isset($foundexpectation['contents'])) {
                $mail->body = quoted_printable_decode($mail->body);
                if (!is_array($foundexpectation['contents'])) {                     $foundexpectation['contents'] = array($foundexpectation['contents']);
                }
                foreach ($foundexpectation['contents'] as $content) {
                    if (strpos($content, '~') !== 0) {
                        $this->assertRegexp('#' . $content . '#m', $mail->body);
                    } else {
                        preg_match('#' . substr($content, 1) . '#m', $mail->body, $matches);
                        $this->assertNotRegexp('#' . substr($content, 1) . '#m', $mail->body);
                    }
                }
            }
        }
                $this->assertCount(0, $expectations);
    }
}
