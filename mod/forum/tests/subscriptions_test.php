<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/forum/lib.php');

class mod_forum_subscriptions_testcase extends advanced_testcase {

    
    public function setUp() {
                        \mod_forum\subscriptions::reset_forum_cache();
        \mod_forum\subscriptions::reset_discussion_cache();
    }

    
    public function tearDown() {
                        \mod_forum\subscriptions::reset_forum_cache();
        \mod_forum\subscriptions::reset_discussion_cache();
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

    
    protected function helper_post_to_forum($forum, $author) {
        global $DB;
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_forum');

                $record = new stdClass();
        $record->course = $forum->course;
        $record->userid = $author->id;
        $record->forum = $forum->id;
        $discussion = $generator->create_discussion($record);

                $post = $DB->get_record('forum_posts', array('discussion' => $discussion->id));

        return array($discussion, $post);
    }

    public function test_subscription_modes() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

        \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_FORCESUBSCRIBE);
        $forum = $DB->get_record('forum', array('id' => $forum->id));
        $this->assertEquals(FORUM_FORCESUBSCRIBE, \mod_forum\subscriptions::get_subscription_mode($forum));
        $this->assertTrue(\mod_forum\subscriptions::is_forcesubscribed($forum));
        $this->assertFalse(\mod_forum\subscriptions::is_subscribable($forum));
        $this->assertFalse(\mod_forum\subscriptions::subscription_disabled($forum));

        \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_DISALLOWSUBSCRIBE);
        $forum = $DB->get_record('forum', array('id' => $forum->id));
        $this->assertEquals(FORUM_DISALLOWSUBSCRIBE, \mod_forum\subscriptions::get_subscription_mode($forum));
        $this->assertTrue(\mod_forum\subscriptions::subscription_disabled($forum));
        $this->assertFalse(\mod_forum\subscriptions::is_subscribable($forum));
        $this->assertFalse(\mod_forum\subscriptions::is_forcesubscribed($forum));

        \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_INITIALSUBSCRIBE);
        $forum = $DB->get_record('forum', array('id' => $forum->id));
        $this->assertEquals(FORUM_INITIALSUBSCRIBE, \mod_forum\subscriptions::get_subscription_mode($forum));
        $this->assertTrue(\mod_forum\subscriptions::is_subscribable($forum));
        $this->assertFalse(\mod_forum\subscriptions::subscription_disabled($forum));
        $this->assertFalse(\mod_forum\subscriptions::is_forcesubscribed($forum));

        \mod_forum\subscriptions::set_subscription_mode($forum->id, FORUM_CHOOSESUBSCRIBE);
        $forum = $DB->get_record('forum', array('id' => $forum->id));
        $this->assertEquals(FORUM_CHOOSESUBSCRIBE, \mod_forum\subscriptions::get_subscription_mode($forum));
        $this->assertTrue(\mod_forum\subscriptions::is_subscribable($forum));
        $this->assertFalse(\mod_forum\subscriptions::subscription_disabled($forum));
        $this->assertFalse(\mod_forum\subscriptions::is_forcesubscribed($forum));
    }

    
    public function test_unsubscribable_forums() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

                list($user) = $this->helper_create_users($course, 1);

                $this->setUser($user);

                $result = \mod_forum\subscriptions::get_unsubscribable_forums();
        $this->assertEquals(0, count($result));

                $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forceforum = $this->getDataGenerator()->create_module('forum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_DISALLOWSUBSCRIBE);
        $disallowforum = $this->getDataGenerator()->create_module('forum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $chooseforum = $this->getDataGenerator()->create_module('forum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $initialforum = $this->getDataGenerator()->create_module('forum', $options);

                $result = \mod_forum\subscriptions::get_unsubscribable_forums();
        $this->assertEquals(1, count($result));

                \mod_forum\subscriptions::subscribe_user($user->id, $disallowforum);
        \mod_forum\subscriptions::subscribe_user($user->id, $chooseforum);

        $result = \mod_forum\subscriptions::get_unsubscribable_forums();
        $this->assertEquals(3, count($result));

                set_coursemodule_visible($forceforum->cmid, 0);
        set_coursemodule_visible($disallowforum->cmid, 0);
        set_coursemodule_visible($chooseforum->cmid, 0);
        set_coursemodule_visible($initialforum->cmid, 0);
        $result = \mod_forum\subscriptions::get_unsubscribable_forums();
        $this->assertEquals(0, count($result));

                $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $context = \context_course::instance($course->id);
        assign_capability('moodle/course:viewhiddenactivities', CAP_ALLOW, $roleids['student'], $context);
        $context->mark_dirty();

                $result = \mod_forum\subscriptions::get_unsubscribable_forums();
        $this->assertEquals(3, count($result));
    }

    
    public function test_forum_subscribe_toggle_as_other() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 1);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::subscribe_user($author->id, $forum);
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::unsubscribe_user($author->id, $forum);
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                                        forum_subscribe($author->id, $forum->id);
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        forum_unsubscribe($author->id, $forum->id);
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::subscribe_user($author->id, $forum);
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::unsubscribe_user($author->id, $forum);
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::subscribe_user($author->id, $forum, null, true);
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::unsubscribe_user($author->id, $forum, null, true);
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::subscribe_user($author->id, $forum);
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

    }

    
    public function test_forum_discussion_subscription_forum_unsubscribed() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 1);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));
    }

    
    public function test_forum_discussion_subscription_forum_subscribed() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 1);

                        $this->assertInternalType('int', \mod_forum\subscriptions::subscribe_user($author->id, $forum));

                $this->assertTrue(\mod_forum\subscriptions::subscribe_user($author->id, $forum));

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));
    }

    
    public function test_forum_discussion_subscription_forum_unsubscribed_discussion_subscribed() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 1);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $this->assertFalse(\mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion));

                $this->assertTrue(\mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));
    }

    
    public function test_forum_discussion_subscription_forum_subscribed_discussion_unsubscribed() {
        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 2);

                \mod_forum\subscriptions::subscribe_user($author->id, $forum);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));
    }

    
    public function test_forum_discussion_toggle_forum_subscribed() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 2);

                \mod_forum\subscriptions::subscribe_user($author->id, $forum);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertFalse(\mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

                $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(\mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion));

                $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                \mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                \mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));
        $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                $this->assertTrue(\mod_forum\subscriptions::unsubscribe_user($author->id, $forum, null, true));

                $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $author->id,
            'forum'         => $forum->id,
        )));

                $result = \mod_forum\subscriptions::fetch_discussion_subscription($forum->id, $author->id);
        $this->assertInternalType('array', $result);
        $this->assertFalse(isset($result[$discussion->id]));
    }

    
    public function test_forum_discussion_toggle_forum_unsubscribed() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 2);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                list($discussion, $post) = $this->helper_post_to_forum($forum, $author);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertTrue(\mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

                $this->assertFalse(\mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion));

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::subscribe_user_to_discussion($author->id, $discussion);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($author->id, $discussion);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum));

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($author->id, $forum, $discussion->id));

                $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $author->id,
            'discussion'    => $discussion->id,
        )));
    }

    
    public function test_forum_is_subscribed_numeric() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($author) = $this->helper_create_users($course, 1);

                $this->assertFalse(forum_is_subscribed($author->id, $forum->id));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertFalse(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                \mod_forum\subscriptions::subscribe_user($author->id, $forum);

        $this->assertTrue(forum_is_subscribed($author->id, $forum->id));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();

                $this->assertTrue(forum_is_subscribed($author->id, $forum));
        $this->assertEquals(1, count($this->getDebuggingMessages()));
        $this->resetDebugging();
    }

    
    public function test_fetch_subscribed_users_subscriptions() {
        global $DB, $CFG;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $usercount = 5;
        $users = $this->helper_create_users($course, $usercount);

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($subscribers));

                $this->getDataGenerator()->enrol_user($CFG->siteguest, $course->id);
        $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($subscribers));

                $unsubscribedcount = 2;
        for ($i = 0; $i < $unsubscribedcount; $i++) {
            \mod_forum\subscriptions::unsubscribe_user($users[$i]->id, $forum);
        }

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));
    }

    
    public function test_fetch_subscribed_users_forced() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $usercount = 5;
        $users = $this->helper_create_users($course, $usercount);

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($subscribers));
    }

    
    public function test_fetch_subscribed_users_discussion_subscriptions() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $usercount = 5;
        $users = $this->helper_create_users($course, $usercount);

        list($discussion, $post) = $this->helper_post_to_forum($forum, $users[0]);

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($subscribers));
        $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum, 0, null, null, true);
        $this->assertEquals($usercount, count($subscribers));

        \mod_forum\subscriptions::unsubscribe_user_from_discussion($users[0]->id, $discussion);

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($subscribers));

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum, 0, null, null, true);
        $this->assertEquals($usercount, count($subscribers));

                $record = new stdClass();
        $record->userid = $users[2]->id;
        $record->forum = $forum->id;
        $record->discussion = $discussion->id;
        $record->preference = time();
        $DB->insert_record('forum_discussion_subs', $record);

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($subscribers));
        $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum, 0, null, null, true);
        $this->assertEquals($usercount, count($subscribers));

                $unsubscribedcount = 2;
        for ($i = 0; $i < $unsubscribedcount; $i++) {
            \mod_forum\subscriptions::unsubscribe_user($users[$i]->id, $forum);
        }

                $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));
        $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum, 0, null, null, true);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));

                $subscribeddiscussionusers = 1;
        for ($i = 0; $i < $subscribeddiscussionusers; $i++) {
            \mod_forum\subscriptions::subscribe_user_to_discussion($users[$i]->id, $discussion);
        }
        $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount - $unsubscribedcount, count($subscribers));
        $subscribers = \mod_forum\subscriptions::fetch_subscribed_users($forum, 0, null, null, true);
        $this->assertEquals($usercount - $unsubscribedcount + $subscribeddiscussionusers, count($subscribers));
    }

    
    public function test_force_subscribed_to_forum() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $roleids['student']);

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($user->id, $forum));

                $cm = get_coursemodule_from_instance('forum', $forum->id);
        $context = \context_module::instance($cm->id);
        assign_capability('mod/forum:allowforcesubscribe', CAP_PROHIBIT, $roleids['student'], $context);
        $context->mark_dirty();
        $this->assertFalse(has_capability('mod/forum:allowforcesubscribe', $context, $user->id));

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($user->id, $forum));
    }

    
    public function test_subscription_cache_prefill() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $users = $this->helper_create_users($course, 20);

                \mod_forum\subscriptions::reset_forum_cache();

                $startcount = $DB->perf_get_reads();
        $this->assertNull(\mod_forum\subscriptions::fill_subscription_cache($forum->id));
        $postfillcount = $DB->perf_get_reads();
        $this->assertEquals(1, $postfillcount - $startcount);

                        foreach ($users as $user) {
            $this->assertTrue(\mod_forum\subscriptions::fetch_subscription_cache($forum->id, $user->id));
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(0, $finalcount - $postfillcount);
    }

    
    public function test_subscription_cache_fill() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $users = $this->helper_create_users($course, 20);

                \mod_forum\subscriptions::reset_forum_cache();

                $startcount = $DB->perf_get_reads();

                foreach ($users as $user) {
            $this->assertTrue(\mod_forum\subscriptions::fetch_subscription_cache($forum->id, $user->id));
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(20, $finalcount - $startcount);
    }

    
    public function test_discussion_subscription_cache_fill_for_course() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

                $options = array('course' => $course->id, 'forcesubscribe' => FORUM_DISALLOWSUBSCRIBE);
        $disallowforum = $this->getDataGenerator()->create_module('forum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $chooseforum = $this->getDataGenerator()->create_module('forum', $options);
        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $initialforum = $this->getDataGenerator()->create_module('forum', $options);

                $users = $this->helper_create_users($course, 20);
        $user = reset($users);

                \mod_forum\subscriptions::reset_forum_cache();

        $startcount = $DB->perf_get_reads();
        $result = \mod_forum\subscriptions::fill_subscription_cache_for_course($course->id, $user->id);
        $this->assertNull($result);
        $postfillcount = $DB->perf_get_reads();
        $this->assertEquals(1, $postfillcount - $startcount);
        $this->assertFalse(\mod_forum\subscriptions::fetch_subscription_cache($disallowforum->id, $user->id));
        $this->assertFalse(\mod_forum\subscriptions::fetch_subscription_cache($chooseforum->id, $user->id));
        $this->assertTrue(\mod_forum\subscriptions::fetch_subscription_cache($initialforum->id, $user->id));
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(0, $finalcount - $postfillcount);

                foreach ($users as $user) {
            $result = \mod_forum\subscriptions::fill_subscription_cache_for_course($course->id, $user->id);
            $this->assertFalse(\mod_forum\subscriptions::fetch_subscription_cache($disallowforum->id, $user->id));
            $this->assertFalse(\mod_forum\subscriptions::fetch_subscription_cache($chooseforum->id, $user->id));
            $this->assertTrue(\mod_forum\subscriptions::fetch_subscription_cache($initialforum->id, $user->id));
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(count($users), $finalcount - $postfillcount);
    }

    
    public function test_discussion_subscription_cache_prefill() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $users = $this->helper_create_users($course, 20);

                $discussions = array();
        $author = $users[0];
        for ($i = 0; $i < 20; $i++) {
            list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
            $discussions[] = $discussion;
        }

                $forumcount = 0;
        $usercount = 0;
        foreach ($discussions as $data) {
            if ($forumcount % 2) {
                continue;
            }
            foreach ($users as $user) {
                if ($usercount % 2) {
                    continue;
                }
                \mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);
                $usercount++;
            }
            $forumcount++;
        }

                \mod_forum\subscriptions::reset_forum_cache();
        \mod_forum\subscriptions::reset_discussion_cache();

                $startcount = $DB->perf_get_reads();
        $this->assertNull(\mod_forum\subscriptions::fill_discussion_subscription_cache($forum->id));
        $postfillcount = $DB->perf_get_reads();
        $this->assertEquals(1, $postfillcount - $startcount);

                        foreach ($users as $user) {
            $result = \mod_forum\subscriptions::fetch_discussion_subscription($forum->id, $user->id);
            $this->assertInternalType('array', $result);
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(0, $finalcount - $postfillcount);
    }

    
    public function test_discussion_subscription_cache_fill() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $users = $this->helper_create_users($course, 20);

                $discussions = array();
        $author = $users[0];
        for ($i = 0; $i < 20; $i++) {
            list($discussion, $post) = $this->helper_post_to_forum($forum, $author);
            $discussions[] = $discussion;
        }

                $forumcount = 0;
        $usercount = 0;
        foreach ($discussions as $data) {
            if ($forumcount % 2) {
                continue;
            }
            foreach ($users as $user) {
                if ($usercount % 2) {
                    continue;
                }
                \mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);
                $usercount++;
            }
            $forumcount++;
        }

                \mod_forum\subscriptions::reset_forum_cache();
        \mod_forum\subscriptions::reset_discussion_cache();

        $startcount = $DB->perf_get_reads();

                        foreach ($users as $user) {
            $result = \mod_forum\subscriptions::fetch_discussion_subscription($forum->id, $user->id);
            $this->assertInternalType('array', $result);
        }
        $finalcount = $DB->perf_get_reads();
        $this->assertEquals(20, $finalcount - $startcount);
    }

    
    public function test_forum_subscribe_toggle_as_other_repeat_subscriptions() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($user) = $this->helper_create_users($course, 1);

                list($discussion, $post) = $this->helper_post_to_forum($forum, $user);

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($user->id, $forum));

                $this->assertFalse(\mod_forum\subscriptions::is_subscribed($user->id, $forum, $discussion->id));

                $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $user->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));

                        \mod_forum\subscriptions::subscribe_user($user->id, $forum);
        $this->assertEquals(1, $DB->count_records('forum_subscriptions', array(
            'userid'        => $user->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(0, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));

                $this->assertTrue(\mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion));

                $this->assertFalse(\mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion));

                $this->assertTrue(\mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion));

                $this->assertFalse(\mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion));

                $this->assertTrue(\mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion));

                \mod_forum\subscriptions::unsubscribe_user($user->id, $forum);

        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $user->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));

                $this->assertTrue(\mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion));
        $this->assertEquals(0, $DB->count_records('forum_subscriptions', array(
            'userid'        => $user->id,
            'forum'         => $forum->id,
        )));
        $this->assertEquals(1, $DB->count_records('forum_discussion_subs', array(
            'userid'        => $user->id,
            'discussion'    => $discussion->id,
        )));
    }

    
    public function test_is_subscribed_cm() {
        global $DB;

        $this->resetAfterTest(true);

                $course = $this->getDataGenerator()->create_course();

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                list($user) = $this->helper_create_users($course, 1);

                $cm = get_fast_modinfo($forum->course)->instances['forum'][$forum->id];

                get_fast_modinfo(0, 0, true);

                        $this->assertTrue(\mod_forum\subscriptions::is_subscribed($user->id, $forum));
        $this->assertTrue(\mod_forum\subscriptions::is_subscribed($user->id, $forum));

                $basecount = $DB->perf_get_reads();

                $this->assertTrue(\mod_forum\subscriptions::is_subscribed($user->id, $forum, null, $cm));

                        $suppliedcmcount = $DB->perf_get_reads() - $basecount;

                get_fast_modinfo(0, 0, true);
        $basecount = $DB->perf_get_reads();
        $this->assertTrue(\mod_forum\subscriptions::is_subscribed($user->id, $forum));
        $calculatedcmcount = $DB->perf_get_reads() - $basecount;

                $this->assertGreaterThan($suppliedcmcount, $calculatedcmcount);
    }

}
