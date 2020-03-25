<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;

class mod_forum_maildigest_testcase extends advanced_testcase {

    
    protected $helper;

    
    public function setUp() {
        global $CFG;

        $this->helper = new stdClass();

                $this->preventResetByRollback();

                $this->helper->messagesink = $this->redirectMessages();
        $this->helper->mailsink = $this->redirectEmails();

                $messages = $this->helper->messagesink->get_messages();
        $this->assertEquals(0, count($messages));

        $messages = $this->helper->mailsink->get_messages();
        $this->assertEquals(0, count($messages));

                $CFG->digestmailtimelast = 0;

                        $CFG->digestmailtime = -1;

                        $CFG->maxeditingtime = 1;

                        \mod_forum\subscriptions::reset_forum_cache();
        \mod_forum\subscriptions::reset_discussion_cache();
    }

    
    public function tearDown() {
        $this->helper->messagesink->clear();
        $this->helper->messagesink->close();

        $this->helper->mailsink->clear();
        $this->helper->mailsink->close();
    }

    
    protected function helper_setup_user_in_course() {
        global $DB;

        $return = new stdClass();
        $return->courses = new stdClass();
        $return->forums = new stdClass();
        $return->forumids = array();

                $user = $this->getDataGenerator()->create_user();
        $return->user = $user;

                $return->courses->course1 = $this->getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $return->courses->course1->id;
        $record->forcesubscribe = 1;

        $return->forums->forum1 = $this->getDataGenerator()->create_module('forum', $record);
        $return->forumsids[] = $return->forums->forum1->id;

        $return->forums->forum2 = $this->getDataGenerator()->create_module('forum', $record);
        $return->forumsids[] = $return->forums->forum2->id;

                list ($test, $params) = $DB->get_in_or_equal($return->forumsids);

                        $this->getDataGenerator()->enrol_user($return->user->id, $return->courses->course1->id);

        return $return;
    }

    
    protected function helper_force_digest_mail_times() {
        global $CFG, $DB;
                                $sitetimezone = core_date::get_server_timezone();
        $digesttime = usergetmidnight(time(), $sitetimezone) + ($CFG->digestmailtime * 3600) - (60 * 60);
        $DB->set_field('forum_posts', 'modified', $digesttime, array('mailed' => 0));
        $DB->set_field('forum_posts', 'created', $digesttime, array('mailed' => 0));
    }

    
    protected function helper_run_cron_check_count($expected, $individualcount, $digestcount) {
        if ($expected === 0) {
            $this->expectOutputRegex('/(Email digests successfully sent to .* users.){0}/');
        } else {
            $this->expectOutputRegex("/Email digests successfully sent to {$expected} users/");
        }
        forum_cron();

                $messages = $this->helper->messagesink->get_messages();

        $counts = (object) array('digest' => 0, 'individual' => 0);
        foreach ($messages as $message) {
            if (strpos($message->subject, 'forum digest') !== false) {
                $counts->digest++;
            } else {
                $counts->individual++;
            }
        }

        $this->assertEquals($digestcount, $counts->digest);
        $this->assertEquals($individualcount, $counts->individual);
    }

    public function test_set_maildigest() {
        global $DB;

        $this->resetAfterTest(true);

        $helper = $this->helper_setup_user_in_course();
        $user = $helper->user;
        $course1 = $helper->courses->course1;
        $forum1 = $helper->forums->forum1;

                self::setUser($helper->user);

                $currentsetting = $DB->get_record('forum_digests', array(
            'forum' => $forum1->id,
            'userid' => $user->id,
        ));
        $this->assertFalse($currentsetting);

                        forum_set_user_maildigest($forum1, 0, $user);
        $currentsetting = $DB->get_record('forum_digests', array(
            'forum' => $forum1->id,
            'userid' => $user->id,
        ));
        $this->assertEquals($currentsetting->maildigest, 0);

        forum_set_user_maildigest($forum1, 1, $user);
        $currentsetting = $DB->get_record('forum_digests', array(
            'forum' => $forum1->id,
            'userid' => $user->id,
        ));
        $this->assertEquals($currentsetting->maildigest, 1);

        forum_set_user_maildigest($forum1, 2, $user);
        $currentsetting = $DB->get_record('forum_digests', array(
            'forum' => $forum1->id,
            'userid' => $user->id,
        ));
        $this->assertEquals($currentsetting->maildigest, 2);

                forum_set_user_maildigest($forum1, -1, $user);
        $currentsetting = $DB->get_record('forum_digests', array(
            'forum' => $forum1->id,
            'userid' => $user->id,
        ));
        $this->assertFalse($currentsetting);

                $this->setExpectedException('moodle_exception');
        forum_set_user_maildigest($forum1, 42, $user);
    }

    public function test_get_user_digest_options_default() {
        global $USER, $DB;

        $this->resetAfterTest(true);

                $helper = $this->helper_setup_user_in_course();
        $user = $helper->user;
        $course1 = $helper->courses->course1;
        $forum1 = $helper->forums->forum1;

                self::setUser($helper->user);

                $digestoptions = array(
            '0' => get_string('emaildigestoffshort', 'mod_forum'),
            '1' => get_string('emaildigestcompleteshort', 'mod_forum'),
            '2' => get_string('emaildigestsubjectsshort', 'mod_forum'),
        );

                $this->assertEquals(0, $user->maildigest);
        $options = forum_get_user_digest_options();
        $this->assertEquals($options[-1], get_string('emaildigestdefault', 'mod_forum', $digestoptions[0]));

                $USER->maildigest = 1;
        $this->assertEquals(1, $USER->maildigest);
        $options = forum_get_user_digest_options();
        $this->assertEquals($options[-1], get_string('emaildigestdefault', 'mod_forum', $digestoptions[1]));

                $USER->maildigest = 2;
        $this->assertEquals(2, $USER->maildigest);
        $options = forum_get_user_digest_options();
        $this->assertEquals($options[-1], get_string('emaildigestdefault', 'mod_forum', $digestoptions[2]));
    }

    public function test_get_user_digest_options_sorting() {
        global $USER, $DB;

        $this->resetAfterTest(true);

                $helper = $this->helper_setup_user_in_course();
        $user = $helper->user;
        $course1 = $helper->courses->course1;
        $forum1 = $helper->forums->forum1;

                self::setUser($helper->user);

                $options = forum_get_user_digest_options();

                $lastoption = -2;
        foreach ($options as $value => $description) {
            $this->assertGreaterThan($lastoption, $value);
            $lastoption = $value;
        }
    }

    public function test_cron_no_posts() {
        global $DB;

        $this->resetAfterTest(true);

        $this->helper_force_digest_mail_times();

                $this->helper_run_cron_check_count(0, 0, 0);
    }

    
    public function test_cron_profile_single_mails() {
        global $DB;

        $this->resetAfterTest(true);

                $userhelper = $this->helper_setup_user_in_course();
        $user = $userhelper->user;
        $course1 = $userhelper->courses->course1;
        $forum1 = $userhelper->forums->forum1;
        $forum2 = $userhelper->forums->forum2;

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user->id;
        $record->mailnow = 1;

                $record->forum = $forum1->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $record->forum = $forum2->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $this->helper_force_digest_mail_times();

                $DB->set_field('user', 'maildigest', 0, array('id' => $user->id));

                forum_set_user_maildigest($forum1, -1, $user);

                forum_set_user_maildigest($forum2, -1, $user);

                $this->helper_run_cron_check_count(0, 10, 0);
    }

    
    public function test_cron_profile_digest_email() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

                $userhelper = $this->helper_setup_user_in_course();
        $user = $userhelper->user;
        $course1 = $userhelper->courses->course1;
        $forum1 = $userhelper->forums->forum1;
        $forum2 = $userhelper->forums->forum2;

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user->id;
        $record->mailnow = 1;

                $record->forum = $forum1->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $record->forum = $forum2->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $this->helper_force_digest_mail_times();

                $DB->set_field('user', 'maildigest', 1, array('id' => $user->id));

                forum_set_user_maildigest($forum1, -1, $user);

                forum_set_user_maildigest($forum2, -1, $user);

                $this->helper_run_cron_check_count(1, 0, 1);
    }

    
    public function test_cron_mixed_email_1() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

                $userhelper = $this->helper_setup_user_in_course();
        $user = $userhelper->user;
        $course1 = $userhelper->courses->course1;
        $forum1 = $userhelper->forums->forum1;
        $forum2 = $userhelper->forums->forum2;

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user->id;
        $record->mailnow = 1;

                $record->forum = $forum1->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $record->forum = $forum2->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $this->helper_force_digest_mail_times();

                $DB->set_field('user', 'maildigest', 0, array('id' => $user->id));

                forum_set_user_maildigest($forum1, 1, $user);

                forum_set_user_maildigest($forum2, -1, $user);

                $this->helper_run_cron_check_count(1, 5, 1);
    }

    
    public function test_cron_mixed_email_2() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

                $userhelper = $this->helper_setup_user_in_course();
        $user = $userhelper->user;
        $course1 = $userhelper->courses->course1;
        $forum1 = $userhelper->forums->forum1;
        $forum2 = $userhelper->forums->forum2;

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user->id;
        $record->mailnow = 1;

                $record->forum = $forum1->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $record->forum = $forum2->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $this->helper_force_digest_mail_times();

                $DB->set_field('user', 'maildigest', 1, array('id' => $user->id));

                forum_set_user_maildigest($forum1, -1, $user);

                forum_set_user_maildigest($forum2, 0, $user);

                $this->helper_run_cron_check_count(1, 5, 1);
    }

    
    public function test_cron_forum_digest_email() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

                $userhelper = $this->helper_setup_user_in_course();
        $user = $userhelper->user;
        $course1 = $userhelper->courses->course1;
        $forum1 = $userhelper->forums->forum1;
        $forum2 = $userhelper->forums->forum2;

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user->id;
        $record->mailnow = 1;

                $record->forum = $forum1->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $record->forum = $forum2->id;
        for ($i = 0; $i < 5; $i++) {
            $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        }

                $this->helper_force_digest_mail_times();

                $DB->set_field('user', 'maildigest', 0, array('id' => $user->id));

                forum_set_user_maildigest($forum1, 1, $user);

                forum_set_user_maildigest($forum2, 2, $user);

                $this->helper_run_cron_check_count(1, 0, 1);
    }

}
