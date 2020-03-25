<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/forum/lib.php');
require_once($CFG->dirroot . '/rating/lib.php');

class mod_forum_lib_testcase extends advanced_testcase {

    public function setUp() {
                        \mod_forum\subscriptions::reset_forum_cache();
    }

    public function tearDown() {
                        \mod_forum\subscriptions::reset_forum_cache();
    }

    public function test_forum_trigger_content_uploaded_event() {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);

        $this->setUser($user->id);
        $fakepost = (object) array('id' => 123, 'message' => 'Yay!', 'discussion' => 100);
        $cm = get_coursemodule_from_instance('forum', $forum->id);

        $fs = get_file_storage();
        $dummy = (object) array(
            'contextid' => $context->id,
            'component' => 'mod_forum',
            'filearea' => 'attachment',
            'itemid' => $fakepost->id,
            'filepath' => '/',
            'filename' => 'myassignmnent.pdf'
        );
        $fi = $fs->create_file_from_string($dummy, 'Content of ' . $dummy->filename);

        $data = new stdClass();
        $sink = $this->redirectEvents();
        forum_trigger_content_uploaded_event($fakepost, $cm, 'some triggered from value');
        $events = $sink->get_events();

        $this->assertCount(1, $events);
        $event = reset($events);
        $this->assertInstanceOf('\mod_forum\event\assessable_uploaded', $event);
        $this->assertEquals($context->id, $event->contextid);
        $this->assertEquals($fakepost->id, $event->objectid);
        $this->assertEquals($fakepost->message, $event->other['content']);
        $this->assertEquals($fakepost->discussion, $event->other['discussionid']);
        $this->assertCount(1, $event->other['pathnamehashes']);
        $this->assertEquals($fi->get_pathnamehash(), $event->other['pathnamehashes'][0]);
        $expected = new stdClass();
        $expected->modulename = 'forum';
        $expected->name = 'some triggered from value';
        $expected->cmid = $forum->cmid;
        $expected->itemid = $fakepost->id;
        $expected->courseid = $course->id;
        $expected->userid = $user->id;
        $expected->content = $fakepost->message;
        $expected->pathnamehashes = array($fi->get_pathnamehash());
        $this->assertEventLegacyData($expected, $event);
        $this->assertEventContextNotUsed($event);
    }

    public function test_forum_get_courses_user_posted_in() {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $course3 = $this->getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course1->id;
        $forum1 = $this->getDataGenerator()->create_module('forum', $record);

        $record = new stdClass();
        $record->course = $course2->id;
        $forum2 = $this->getDataGenerator()->create_module('forum', $record);

        $record = new stdClass();
        $record->course = $course3->id;
        $forum3 = $this->getDataGenerator()->create_module('forum', $record);

                $record = new stdClass();
        $record->course = $course1->id;
        $forum4 = $this->getDataGenerator()->create_module('forum', $record);

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $user1->id;
        $record->forum = $forum4->id;
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = new stdClass();
        $record->course = $course2->id;
        $record->userid = $user1->id;
        $record->forum = $forum2->id;
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = new stdClass();
        $record->course = $course3->id;
        $record->userid = $user2->id;
        $record->forum = $forum3->id;
        $discussion3 = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = new stdClass();
        $record->course = $course3->id;
        $record->userid = $user1->id;
        $record->forum = $forum3->id;
        $record->discussion = $discussion3->id;
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $user3courses = forum_get_courses_user_posted_in($user3);
        $this->assertEmpty($user3courses);

                $user2courses = forum_get_courses_user_posted_in($user2);
        $this->assertCount(1, $user2courses);
        $user2course = array_shift($user2courses);
        $this->assertEquals($course3->id, $user2course->id);
        $this->assertEquals($course3->shortname, $user2course->shortname);

                $user1courses = forum_get_courses_user_posted_in($user1);
        $this->assertCount(3, $user1courses);
        foreach ($user1courses as $course) {
            $this->assertContains($course->id, array($course1->id, $course2->id, $course3->id));
            $this->assertContains($course->shortname, array($course1->shortname, $course2->shortname,
                $course3->shortname));

        }

                $user1courses = forum_get_courses_user_posted_in($user1, true);
        $this->assertCount(2, $user1courses);
        foreach ($user1courses as $course) {
            $this->assertContains($course->id, array($course1->id, $course2->id));
            $this->assertContains($course->shortname, array($course1->shortname, $course2->shortname));
        }
    }

    
    public function test_forum_tp_can_track_forums() {
        global $CFG;

        $this->resetAfterTest();

        $useron = $this->getDataGenerator()->create_user(array('trackforums' => 1));
        $useroff = $this->getDataGenerator()->create_user(array('trackforums' => 0));
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OFF);         $forumoff = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_FORCED);         $forumforce = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OPTIONAL);         $forumoptional = $this->getDataGenerator()->create_module('forum', $options);

                $CFG->forum_allowforcedreadtracking = 1;

                $result = forum_tp_can_track_forums($forumoff, $useron);
        $this->assertEquals(false, $result);

                $result = forum_tp_can_track_forums($forumforce, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_can_track_forums($forumoptional, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_can_track_forums($forumoff, $useroff);
        $this->assertEquals(false, $result);

                $result = forum_tp_can_track_forums($forumforce, $useroff);
        $this->assertEquals(true, $result);

                $result = forum_tp_can_track_forums($forumoptional, $useroff);
        $this->assertEquals(false, $result);

                $CFG->forum_allowforcedreadtracking = 0;

                $result = forum_tp_can_track_forums($forumoff, $useron);
        $this->assertEquals(false, $result);

                $result = forum_tp_can_track_forums($forumforce, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_can_track_forums($forumoptional, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_can_track_forums($forumoff, $useroff);
        $this->assertEquals(false, $result);

                $result = forum_tp_can_track_forums($forumforce, $useroff);
        $this->assertEquals(false, $result);

                $result = forum_tp_can_track_forums($forumoptional, $useroff);
        $this->assertEquals(false, $result);

    }

    
    public function test_forum_tp_is_tracked() {
        global $CFG;

        $this->resetAfterTest();

        $useron = $this->getDataGenerator()->create_user(array('trackforums' => 1));
        $useroff = $this->getDataGenerator()->create_user(array('trackforums' => 0));
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OFF);         $forumoff = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_FORCED);         $forumforce = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OPTIONAL);         $forumoptional = $this->getDataGenerator()->create_module('forum', $options);

                $CFG->forum_allowforcedreadtracking = 1;

                $result = forum_tp_is_tracked($forumoff, $useron);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumforce, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_is_tracked($forumoptional, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_is_tracked($forumoff, $useroff);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumforce, $useroff);
        $this->assertEquals(true, $result);

                $result = forum_tp_is_tracked($forumoptional, $useroff);
        $this->assertEquals(false, $result);

                $CFG->forum_allowforcedreadtracking = 0;

                $result = forum_tp_is_tracked($forumoff, $useron);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumforce, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_is_tracked($forumoptional, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_is_tracked($forumoff, $useroff);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumforce, $useroff);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumoptional, $useroff);
        $this->assertEquals(false, $result);

                forum_tp_stop_tracking($forumforce->id, $useron->id);
        forum_tp_stop_tracking($forumoptional->id, $useron->id);
        forum_tp_stop_tracking($forumforce->id, $useroff->id);
        forum_tp_stop_tracking($forumoptional->id, $useroff->id);

                $CFG->forum_allowforcedreadtracking = 1;

                $result = forum_tp_is_tracked($forumforce, $useron);
        $this->assertEquals(true, $result);

                $result = forum_tp_is_tracked($forumoptional, $useron);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumforce, $useroff);
        $this->assertEquals(true, $result);

                $result = forum_tp_is_tracked($forumoptional, $useroff);
        $this->assertEquals(false, $result);

                $CFG->forum_allowforcedreadtracking = 0;

                $result = forum_tp_is_tracked($forumforce, $useron);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumoptional, $useron);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumforce, $useroff);
        $this->assertEquals(false, $result);

                $result = forum_tp_is_tracked($forumoptional, $useroff);
        $this->assertEquals(false, $result);
    }

    
    public function test_forum_tp_get_course_unread_posts() {
        global $CFG;

        $this->resetAfterTest();

        $useron = $this->getDataGenerator()->create_user(array('trackforums' => 1));
        $useroff = $this->getDataGenerator()->create_user(array('trackforums' => 0));
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OFF);         $forumoff = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_FORCED);         $forumforce = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OPTIONAL);         $forumoptional = $this->getDataGenerator()->create_module('forum', $options);

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $useron->id;
        $record->forum = $forumoff->id;
        $discussionoff = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $useron->id;
        $record->forum = $forumforce->id;
        $discussionforce = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $useroff->id;
        $record->forum = $forumforce->id;
        $record->discussion = $discussionforce->id;
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $useron->id;
        $record->forum = $forumoptional->id;
        $discussionoptional = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $CFG->forum_allowforcedreadtracking = 1;

        $result = forum_tp_get_course_unread_posts($useron->id, $course->id);
        $this->assertEquals(2, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));
        $this->assertEquals(2, $result[$forumforce->id]->unread);
        $this->assertEquals(true, isset($result[$forumoptional->id]));
        $this->assertEquals(1, $result[$forumoptional->id]->unread);

        $result = forum_tp_get_course_unread_posts($useroff->id, $course->id);
        $this->assertEquals(1, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));
        $this->assertEquals(2, $result[$forumforce->id]->unread);
        $this->assertEquals(false, isset($result[$forumoptional->id]));

                $CFG->forum_allowforcedreadtracking = 0;

        $result = forum_tp_get_course_unread_posts($useron->id, $course->id);
        $this->assertEquals(2, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));
        $this->assertEquals(2, $result[$forumforce->id]->unread);
        $this->assertEquals(true, isset($result[$forumoptional->id]));
        $this->assertEquals(1, $result[$forumoptional->id]->unread);

        $result = forum_tp_get_course_unread_posts($useroff->id, $course->id);
        $this->assertEquals(0, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(false, isset($result[$forumforce->id]));
        $this->assertEquals(false, isset($result[$forumoptional->id]));

                forum_tp_stop_tracking($forumforce->id, $useron->id);
        forum_tp_stop_tracking($forumoptional->id, $useron->id);
        forum_tp_stop_tracking($forumforce->id, $useroff->id);
        forum_tp_stop_tracking($forumoptional->id, $useroff->id);

                $CFG->forum_allowforcedreadtracking = 1;

        $result = forum_tp_get_course_unread_posts($useron->id, $course->id);
        $this->assertEquals(1, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));
        $this->assertEquals(2, $result[$forumforce->id]->unread);
        $this->assertEquals(false, isset($result[$forumoptional->id]));

        $result = forum_tp_get_course_unread_posts($useroff->id, $course->id);
        $this->assertEquals(1, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));
        $this->assertEquals(2, $result[$forumforce->id]->unread);
        $this->assertEquals(false, isset($result[$forumoptional->id]));

                $CFG->forum_allowforcedreadtracking = 0;

        $result = forum_tp_get_course_unread_posts($useron->id, $course->id);
        $this->assertEquals(0, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(false, isset($result[$forumforce->id]));
        $this->assertEquals(false, isset($result[$forumoptional->id]));

        $result = forum_tp_get_course_unread_posts($useroff->id, $course->id);
        $this->assertEquals(0, count($result));
        $this->assertEquals(false, isset($result[$forumoff->id]));
        $this->assertEquals(false, isset($result[$forumforce->id]));
        $this->assertEquals(false, isset($result[$forumoptional->id]));
    }

    
    public function test_forum_tp_get_untracked_forums() {
        global $CFG;

        $this->resetAfterTest();

        $useron = $this->getDataGenerator()->create_user(array('trackforums' => 1));
        $useroff = $this->getDataGenerator()->create_user(array('trackforums' => 0));
        $course = $this->getDataGenerator()->create_course();
        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OFF);         $forumoff = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_FORCED);         $forumforce = $this->getDataGenerator()->create_module('forum', $options);

        $options = array('course' => $course->id, 'trackingtype' => FORUM_TRACKING_OPTIONAL);         $forumoptional = $this->getDataGenerator()->create_module('forum', $options);

                $CFG->forum_allowforcedreadtracking = 1;

                $result = forum_tp_get_untracked_forums($useron->id, $course->id);
        $this->assertEquals(1, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));

                $result = forum_tp_get_untracked_forums($useroff->id, $course->id);
        $this->assertEquals(2, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumoptional->id]));

                $CFG->forum_allowforcedreadtracking = 0;

                $result = forum_tp_get_untracked_forums($useron->id, $course->id);
        $this->assertEquals(1, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));

                $result = forum_tp_get_untracked_forums($useroff->id, $course->id);
        $this->assertEquals(3, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumoptional->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));

                forum_tp_stop_tracking($forumforce->id, $useron->id);
        forum_tp_stop_tracking($forumoptional->id, $useron->id);
        forum_tp_stop_tracking($forumforce->id, $useroff->id);
        forum_tp_stop_tracking($forumoptional->id, $useroff->id);

                $CFG->forum_allowforcedreadtracking = 1;

                $result = forum_tp_get_untracked_forums($useron->id, $course->id);
        $this->assertEquals(2, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumoptional->id]));

                $result = forum_tp_get_untracked_forums($useroff->id, $course->id);
        $this->assertEquals(2, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumoptional->id]));

                $CFG->forum_allowforcedreadtracking = 0;

                $result = forum_tp_get_untracked_forums($useron->id, $course->id);
        $this->assertEquals(3, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumoptional->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));

                $result = forum_tp_get_untracked_forums($useroff->id, $course->id);
        $this->assertEquals(3, count($result));
        $this->assertEquals(true, isset($result[$forumoff->id]));
        $this->assertEquals(true, isset($result[$forumoptional->id]));
        $this->assertEquals(true, isset($result[$forumforce->id]));
    }

    
    public function test_forum_auto_subscribe_on_create() {
        global $CFG;

        $this->resetAfterTest();

        $usercount = 5;
        $course = $this->getDataGenerator()->create_course();
        $users = array();

        for ($i = 0; $i < $usercount; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $users[] = $user;
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
        }

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);         $forum = $this->getDataGenerator()->create_module('forum', $options);

        $result = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($result));
        foreach ($users as $user) {
            $this->assertTrue(\mod_forum\subscriptions::is_subscribed($user->id, $forum));
        }
    }

    
    public function test_forum_forced_subscribe_on_create() {
        global $CFG;

        $this->resetAfterTest();

        $usercount = 5;
        $course = $this->getDataGenerator()->create_course();
        $users = array();

        for ($i = 0; $i < $usercount; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $users[] = $user;
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
        }

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_FORCESUBSCRIBE);         $forum = $this->getDataGenerator()->create_module('forum', $options);

        $result = \mod_forum\subscriptions::fetch_subscribed_users($forum);
        $this->assertEquals($usercount, count($result));
        foreach ($users as $user) {
            $this->assertTrue(\mod_forum\subscriptions::is_subscribed($user->id, $forum));
        }
    }

    
    public function test_forum_optional_subscribe_on_create() {
        global $CFG;

        $this->resetAfterTest();

        $usercount = 5;
        $course = $this->getDataGenerator()->create_course();
        $users = array();

        for ($i = 0; $i < $usercount; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $users[] = $user;
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
        }

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);         $forum = $this->getDataGenerator()->create_module('forum', $options);

        $result = \mod_forum\subscriptions::fetch_subscribed_users($forum);
                $this->assertEquals(0, count($result));
        foreach ($users as $user) {
            $this->assertFalse(\mod_forum\subscriptions::is_subscribed($user->id, $forum));
        }
    }

    
    public function test_forum_disallow_subscribe_on_create() {
        global $CFG;

        $this->resetAfterTest();

        $usercount = 5;
        $course = $this->getDataGenerator()->create_course();
        $users = array();

        for ($i = 0; $i < $usercount; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $users[] = $user;
            $this->getDataGenerator()->enrol_user($user->id, $course->id);
        }

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_DISALLOWSUBSCRIBE);         $forum = $this->getDataGenerator()->create_module('forum', $options);

        $result = \mod_forum\subscriptions::fetch_subscribed_users($forum);
                $this->assertEquals(0, count($result));
        foreach ($users as $user) {
            $this->assertFalse(\mod_forum\subscriptions::is_subscribed($user->id, $forum));
        }
    }

    
    public function test_forum_get_context() {
        global $DB, $PAGE;

        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);
        $forumcm = get_coursemodule_from_instance('forum', $forum->id);
        $forumcontext = \context_module::instance($forumcm->id);

                                $startcount = $DB->perf_get_reads();
        $result = forum_get_context($forum->id, $forumcontext);
        $aftercount = $DB->perf_get_reads();
        $this->assertEquals($forumcontext, $result);
        $this->assertEquals(0, $aftercount - $startcount);

                        $startcount = $DB->perf_get_reads();
        $result = forum_get_context($forum->id, $coursecontext);
        $aftercount = $DB->perf_get_reads();
        $this->assertEquals($forumcontext, $result);
        $this->assertEquals(1, $aftercount - $startcount);

                        $startcount = $DB->perf_get_reads();
        $result = forum_get_context($forum->id);
        $aftercount = $DB->perf_get_reads();
        $this->assertEquals($forumcontext, $result);
        $this->assertEquals(1, $aftercount - $startcount);

                $PAGE = new moodle_page();
        $PAGE->set_context($forumcontext);
        $PAGE->set_cm($forumcm, $course, $forum);

                        $startcount = $DB->perf_get_reads();
        $result = forum_get_context($forum->id, $coursecontext);
        $aftercount = $DB->perf_get_reads();
        $this->assertEquals($forumcontext, $result);
        $this->assertEquals(0, $aftercount - $startcount);

                        $startcount = $DB->perf_get_reads();
        $result = forum_get_context($forum->id);
        $aftercount = $DB->perf_get_reads();
        $this->assertEquals($forumcontext, $result);
        $this->assertEquals(0, $aftercount - $startcount);

                $PAGE = new moodle_page();
        $PAGE->set_context($coursecontext);

                        $startcount = $DB->perf_get_reads();
        $result = forum_get_context($forum->id, $coursecontext);
        $aftercount = $DB->perf_get_reads();
        $this->assertEquals($forumcontext, $result);
        $this->assertEquals(1, $aftercount - $startcount);

                        $startcount = $DB->perf_get_reads();
        $result = forum_get_context($forum->id);
        $aftercount = $DB->perf_get_reads();
        $this->assertEquals($forumcontext, $result);
        $this->assertEquals(1, $aftercount - $startcount);
    }

    
    public function test_forum_get_neighbours() {
        global $CFG, $DB;
        $this->resetAfterTest();

        $timenow = time();
        $timenext = $timenow;

                $forumgen = $this->getDataGenerator()->get_plugin_generator('mod_forum');
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $cm = get_coursemodule_from_instance('forum', $forum->id);
        $context = context_module::instance($cm->id);

        $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        $record->timemodified = time();
        $disc1 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc2 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc3 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc4 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc5 = $forumgen->create_discussion($record);

                $neighbours = forum_get_discussion_neighbours($cm, $disc1, $forum);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc2->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc2, $forum);
        $this->assertEquals($disc1->id, $neighbours['prev']->id);
        $this->assertEquals($disc3->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc3, $forum);
        $this->assertEquals($disc2->id, $neighbours['prev']->id);
        $this->assertEquals($disc4->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc4, $forum);
        $this->assertEquals($disc3->id, $neighbours['prev']->id);
        $this->assertEquals($disc5->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc5, $forum);
        $this->assertEquals($disc4->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                        $record->timemodified++;
        $disc1->timemodified = $record->timemodified;
        $DB->update_record('forum_discussions', $disc1);

        $neighbours = forum_get_discussion_neighbours($cm, $disc5, $forum);
        $this->assertEquals($disc4->id, $neighbours['prev']->id);
        $this->assertEquals($disc1->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc2, $forum);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc3->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc1, $forum);
        $this->assertEquals($disc5->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $record->timemodified++;
        $disc6 = $forumgen->create_discussion($record);
        $neighbours = forum_get_discussion_neighbours($cm, $disc6, $forum);
        $this->assertEquals($disc1->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

        $record->timemodified++;
        $disc7 = $forumgen->create_discussion($record);
        $neighbours = forum_get_discussion_neighbours($cm, $disc7, $forum);
        $this->assertEquals($disc6->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $CFG->forum_enabletimedposts = true;
        $now = $record->timemodified;
        $past = $now - 60;
        $future = $now + 60;

        $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        $record->timestart = $past;
        $record->timeend = $future;
        $record->timemodified = $now;
        $record->timemodified++;
        $disc8 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = $future;
        $record->timeend = 0;
        $disc9 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = 0;
        $record->timeend = 0;
        $disc10 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = 0;
        $record->timeend = $past;
        $disc11 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = $past;
        $record->timeend = $future;
        $disc12 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = $future + 1;         $record->timeend = 0;
        $disc13 = $forumgen->create_discussion($record);

                                                                        $this->setAdminUser();
        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc7->id, $neighbours['prev']->id);
        $this->assertEquals($disc10->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc9, $forum);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc13->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc10, $forum);
        $this->assertEquals($disc8->id, $neighbours['prev']->id);
        $this->assertEquals($disc11->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc11, $forum);
        $this->assertEquals($disc10->id, $neighbours['prev']->id);
        $this->assertEquals($disc12->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc12, $forum);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc9->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc13, $forum);
        $this->assertEquals($disc9->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user);
        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc7->id, $neighbours['prev']->id);
        $this->assertEquals($disc10->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc9, $forum);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc13->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc10, $forum);
        $this->assertEquals($disc8->id, $neighbours['prev']->id);
        $this->assertEquals($disc11->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc11, $forum);
        $this->assertEquals($disc10->id, $neighbours['prev']->id);
        $this->assertEquals($disc12->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc12, $forum);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc9->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc13, $forum);
        $this->assertEquals($disc9->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user2);
        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc7->id, $neighbours['prev']->id);
        $this->assertEquals($disc10->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc10, $forum);
        $this->assertEquals($disc8->id, $neighbours['prev']->id);
        $this->assertEquals($disc12->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc12, $forum);
        $this->assertEquals($disc10->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $CFG->forum_enabletimedposts = false;
        $this->setAdminUser();

                $record->timemodified += 25;
        $DB->update_record('forum_discussions', (object) array('id' => $disc3->id, 'timemodified' => $record->timemodified));
        $DB->update_record('forum_discussions', (object) array('id' => $disc2->id, 'timemodified' => $record->timemodified));
        $DB->update_record('forum_discussions', (object) array('id' => $disc12->id, 'timemodified' => $record->timemodified - 5));
        $disc2 = $DB->get_record('forum_discussions', array('id' => $disc2->id));
        $disc3 = $DB->get_record('forum_discussions', array('id' => $disc3->id));

        $neighbours = forum_get_discussion_neighbours($cm, $disc3, $forum);
        $this->assertEquals($disc2->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

        $neighbours = forum_get_discussion_neighbours($cm, $disc2, $forum);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc3->id, $neighbours['next']->id);

                $DB->update_record('forum_discussions', (object) array('id' => $disc2->id, 'timemodified' => $record->timemodified - 1));

                $disc8->pinned = FORUM_DISCUSSION_PINNED;
        $DB->update_record('forum_discussions', (object) array('id' => $disc8->id, 'pinned' => $disc8->pinned));
        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc3->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

        $neighbours = forum_get_discussion_neighbours($cm, $disc3, $forum);
        $this->assertEquals($disc2->id, $neighbours['prev']->id);
        $this->assertEquals($disc8->id, $neighbours['next']->id);

                $disc6->pinned = FORUM_DISCUSSION_PINNED;
        $DB->update_record('forum_discussions', (object) array('id' => $disc6->id, 'pinned' => $disc6->pinned));
        $disc4->pinned = FORUM_DISCUSSION_PINNED;
        $DB->update_record('forum_discussions', (object) array('id' => $disc4->id, 'pinned' => $disc4->pinned));

        $neighbours = forum_get_discussion_neighbours($cm, $disc6, $forum);
        $this->assertEquals($disc4->id, $neighbours['prev']->id);
        $this->assertEquals($disc8->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc4, $forum);
        $this->assertEquals($disc3->id, $neighbours['prev']->id);
        $this->assertEquals($disc6->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc6->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
    }

    
    public function test_forum_get_neighbours_blog() {
        global $CFG, $DB;
        $this->resetAfterTest();

        $timenow = time();
        $timenext = $timenow;

                $forumgen = $this->getDataGenerator()->get_plugin_generator('mod_forum');
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'type' => 'blog'));
        $cm = get_coursemodule_from_instance('forum', $forum->id);
        $context = context_module::instance($cm->id);

        $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        $record->timemodified = time();
        $disc1 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc2 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc3 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc4 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $disc5 = $forumgen->create_discussion($record);

                $neighbours = forum_get_discussion_neighbours($cm, $disc1, $forum);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc2->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc2, $forum);
        $this->assertEquals($disc1->id, $neighbours['prev']->id);
        $this->assertEquals($disc3->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc3, $forum);
        $this->assertEquals($disc2->id, $neighbours['prev']->id);
        $this->assertEquals($disc4->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc4, $forum);
        $this->assertEquals($disc3->id, $neighbours['prev']->id);
        $this->assertEquals($disc5->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc5, $forum);
        $this->assertEquals($disc4->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $record->timemodified++;
        $disc1->timemodified = $record->timemodified;
        $DB->update_record('forum_discussions', $disc1);

        $neighbours = forum_get_discussion_neighbours($cm, $disc1, $forum);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc2->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc2, $forum);
        $this->assertEquals($disc1->id, $neighbours['prev']->id);
        $this->assertEquals($disc3->id, $neighbours['next']->id);

                $record->timemodified++;
        $disc6 = $forumgen->create_discussion($record);
        $neighbours = forum_get_discussion_neighbours($cm, $disc6, $forum);
        $this->assertEquals($disc5->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

        $record->timemodified++;
        $disc7 = $forumgen->create_discussion($record);
        $neighbours = forum_get_discussion_neighbours($cm, $disc7, $forum);
        $this->assertEquals($disc6->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $CFG->forum_enabletimedposts = true;
        $now = $record->timemodified;
        $past = $now - 60;
        $future = $now + 60;

        $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        $record->timestart = $past;
        $record->timeend = $future;
        $record->timemodified = $now;
        $record->timemodified++;
        $disc8 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = $future;
        $record->timeend = 0;
        $disc9 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = 0;
        $record->timeend = 0;
        $disc10 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = 0;
        $record->timeend = $past;
        $disc11 = $forumgen->create_discussion($record);
        $record->timemodified++;
        $record->timestart = $past;
        $record->timeend = $future;
        $disc12 = $forumgen->create_discussion($record);

                $this->setAdminUser();
        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc7->id, $neighbours['prev']->id);
        $this->assertEquals($disc9->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc9, $forum);
        $this->assertEquals($disc8->id, $neighbours['prev']->id);
        $this->assertEquals($disc10->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc10, $forum);
        $this->assertEquals($disc9->id, $neighbours['prev']->id);
        $this->assertEquals($disc11->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc11, $forum);
        $this->assertEquals($disc10->id, $neighbours['prev']->id);
        $this->assertEquals($disc12->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc12, $forum);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user);
        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc7->id, $neighbours['prev']->id);
        $this->assertEquals($disc9->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc9, $forum);
        $this->assertEquals($disc8->id, $neighbours['prev']->id);
        $this->assertEquals($disc10->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc10, $forum);
        $this->assertEquals($disc9->id, $neighbours['prev']->id);
        $this->assertEquals($disc11->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc11, $forum);
        $this->assertEquals($disc10->id, $neighbours['prev']->id);
        $this->assertEquals($disc12->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc12, $forum);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user2);
        $neighbours = forum_get_discussion_neighbours($cm, $disc8, $forum);
        $this->assertEquals($disc7->id, $neighbours['prev']->id);
        $this->assertEquals($disc10->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc10, $forum);
        $this->assertEquals($disc8->id, $neighbours['prev']->id);
        $this->assertEquals($disc12->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc12, $forum);
        $this->assertEquals($disc10->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $CFG->forum_enabletimedposts = false;
        $this->setAdminUser();

        $record->timemodified++;
                $DB->update_record('forum_posts', (object) array('id' => $disc2->firstpost, 'created' => $record->timemodified));
        $DB->update_record('forum_posts', (object) array('id' => $disc3->firstpost, 'created' => $record->timemodified));

        $neighbours = forum_get_discussion_neighbours($cm, $disc2, $forum);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc3->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm, $disc3, $forum);
        $this->assertEquals($disc2->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
    }

    
    public function test_forum_get_neighbours_with_groups() {
        $this->resetAfterTest();

        $timenow = time();
        $timenext = $timenow;

                $forumgen = $this->getDataGenerator()->get_plugin_generator('mod_forum');
        $course = $this->getDataGenerator()->create_course();
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);
        $this->getDataGenerator()->create_group_member(array('userid' => $user1->id, 'groupid' => $group1->id));

        $forum1 = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'groupmode' => VISIBLEGROUPS));
        $forum2 = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'groupmode' => SEPARATEGROUPS));
        $cm1 = get_coursemodule_from_instance('forum', $forum1->id);
        $cm2 = get_coursemodule_from_instance('forum', $forum2->id);
        $context1 = context_module::instance($cm1->id);
        $context2 = context_module::instance($cm2->id);

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $record->groupid = $group1->id;
        $record->timemodified = time();
        $disc11 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $record->timemodified++;
        $disc21 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user2->id;
        $record->forum = $forum1->id;
        $record->groupid = $group2->id;
        $disc12 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc22 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $record->groupid = null;
        $disc13 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc23 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user2->id;
        $record->forum = $forum1->id;
        $record->groupid = $group2->id;
        $disc14 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc24 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $record->groupid = $group1->id;
        $disc15 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc25 = $forumgen->create_discussion($record);

                $this->setAdminUser();
        $neighbours = forum_get_discussion_neighbours($cm1, $disc11, $forum1);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc12->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc21, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc22->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc12, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc22, $forum2);
        $this->assertEquals($disc21->id, $neighbours['prev']->id);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc14->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEquals($disc22->id, $neighbours['prev']->id);
        $this->assertEquals($disc24->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc14, $forum1);
        $this->assertEquals($disc13->id, $neighbours['prev']->id);
        $this->assertEquals($disc15->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc24, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEquals($disc25->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc15, $forum1);
        $this->assertEquals($disc14->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc25, $forum2);
        $this->assertEquals($disc24->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $_POST['group'] = $group1->id;
        $this->assertEquals($group1->id, groups_get_activity_group($cm1, true));
        $this->assertEquals($group1->id, groups_get_activity_group($cm2, true));

        $neighbours = forum_get_discussion_neighbours($cm1, $disc11, $forum1);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc21, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc15->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEquals($disc21->id, $neighbours['prev']->id);
        $this->assertEquals($disc25->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc15, $forum1);
        $this->assertEquals($disc13->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc25, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user1);
        $_POST['group'] = 0;
        $this->assertEquals(0, groups_get_activity_group($cm1, true));

                $neighbours = forum_get_discussion_neighbours($cm1, $disc12, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc14->id, $neighbours['next']->id);

                $this->setUser($user2);
        $_POST['group'] = 0;
        $this->assertEquals(0, groups_get_activity_group($cm2, true));

        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEmpty($neighbours['next']);

        $neighbours = forum_get_discussion_neighbours($cm2, $disc22, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm2, $disc24, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user1);
        $_POST['group'] = $group1->id;
        $this->assertEquals($group1->id, groups_get_activity_group($cm1, true));
        $this->assertEquals($group1->id, groups_get_activity_group($cm2, true));

                $neighbours = forum_get_discussion_neighbours($cm1, $disc11, $forum1);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc21, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc15->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEquals($disc21->id, $neighbours['prev']->id);
        $this->assertEquals($disc25->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc15, $forum1);
        $this->assertEquals($disc13->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc25, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setExpectedException('coding_exception');
        forum_get_discussion_neighbours($cm2, $disc11, $forum2);
    }

    
    public function test_forum_get_neighbours_with_groups_blog() {
        $this->resetAfterTest();

        $timenow = time();
        $timenext = $timenow;

                $forumgen = $this->getDataGenerator()->get_plugin_generator('mod_forum');
        $course = $this->getDataGenerator()->create_course();
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);
        $this->getDataGenerator()->create_group_member(array('userid' => $user1->id, 'groupid' => $group1->id));

        $forum1 = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'type' => 'blog',
                'groupmode' => VISIBLEGROUPS));
        $forum2 = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'type' => 'blog',
                'groupmode' => SEPARATEGROUPS));
        $cm1 = get_coursemodule_from_instance('forum', $forum1->id);
        $cm2 = get_coursemodule_from_instance('forum', $forum2->id);
        $context1 = context_module::instance($cm1->id);
        $context2 = context_module::instance($cm2->id);

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $record->groupid = $group1->id;
        $record->timemodified = time();
        $disc11 = $forumgen->create_discussion($record);
        $record->timenow = $timenext++;
        $record->forum = $forum2->id;
        $record->timemodified++;
        $disc21 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user2->id;
        $record->forum = $forum1->id;
        $record->groupid = $group2->id;
        $disc12 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc22 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $record->groupid = null;
        $disc13 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc23 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user2->id;
        $record->forum = $forum1->id;
        $record->groupid = $group2->id;
        $disc14 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc24 = $forumgen->create_discussion($record);

        $record->timemodified++;
        $record->userid = $user1->id;
        $record->forum = $forum1->id;
        $record->groupid = $group1->id;
        $disc15 = $forumgen->create_discussion($record);
        $record->forum = $forum2->id;
        $disc25 = $forumgen->create_discussion($record);

                $this->setAdminUser();
        $neighbours = forum_get_discussion_neighbours($cm1, $disc11, $forum1);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc12->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc21, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc22->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc12, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc22, $forum2);
        $this->assertEquals($disc21->id, $neighbours['prev']->id);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc14->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEquals($disc22->id, $neighbours['prev']->id);
        $this->assertEquals($disc24->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc14, $forum1);
        $this->assertEquals($disc13->id, $neighbours['prev']->id);
        $this->assertEquals($disc15->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc24, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEquals($disc25->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc15, $forum1);
        $this->assertEquals($disc14->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc25, $forum2);
        $this->assertEquals($disc24->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $_POST['group'] = $group1->id;
        $this->assertEquals($group1->id, groups_get_activity_group($cm1, true));
        $this->assertEquals($group1->id, groups_get_activity_group($cm2, true));

        $neighbours = forum_get_discussion_neighbours($cm1, $disc11, $forum1);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc21, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc15->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEquals($disc21->id, $neighbours['prev']->id);
        $this->assertEquals($disc25->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc15, $forum1);
        $this->assertEquals($disc13->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc25, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user1);
        $_POST['group'] = 0;
        $this->assertEquals(0, groups_get_activity_group($cm1, true));

                $neighbours = forum_get_discussion_neighbours($cm1, $disc12, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc12->id, $neighbours['prev']->id);
        $this->assertEquals($disc14->id, $neighbours['next']->id);

                $this->setUser($user2);
        $_POST['group'] = 0;
        $this->assertEquals(0, groups_get_activity_group($cm2, true));

        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEmpty($neighbours['next']);

        $neighbours = forum_get_discussion_neighbours($cm2, $disc22, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm2, $disc24, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setUser($user1);
        $_POST['group'] = $group1->id;
        $this->assertEquals($group1->id, groups_get_activity_group($cm1, true));
        $this->assertEquals($group1->id, groups_get_activity_group($cm2, true));

                $neighbours = forum_get_discussion_neighbours($cm1, $disc11, $forum1);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc13->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc21, $forum2);
        $this->assertEmpty($neighbours['prev']);
        $this->assertEquals($disc23->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc13, $forum1);
        $this->assertEquals($disc11->id, $neighbours['prev']->id);
        $this->assertEquals($disc15->id, $neighbours['next']->id);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc23, $forum2);
        $this->assertEquals($disc21->id, $neighbours['prev']->id);
        $this->assertEquals($disc25->id, $neighbours['next']->id);

        $neighbours = forum_get_discussion_neighbours($cm1, $disc15, $forum1);
        $this->assertEquals($disc13->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);
        $neighbours = forum_get_discussion_neighbours($cm2, $disc25, $forum2);
        $this->assertEquals($disc23->id, $neighbours['prev']->id);
        $this->assertEmpty($neighbours['next']);

                $this->setExpectedException('coding_exception');
        forum_get_discussion_neighbours($cm2, $disc11, $forum2);
    }

    public function test_count_discussion_replies_basic() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);

                $result = forum_count_discussion_replies($forum->id);
        $this->assertCount(10, $result);
    }

    public function test_count_discussion_replies_limited() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
                $result = forum_count_discussion_replies($forum->id, "", 20);
        $this->assertCount(10, $result);
    }

    public function test_count_discussion_replies_paginated() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
                $result = forum_count_discussion_replies($forum->id, "", -1, 0, 100);
        $this->assertCount(10, $result);
    }

    public function test_count_discussion_replies_paginated_sorted() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
                $result = forum_count_discussion_replies($forum->id, "d.id asc", -1, 0, 100);
        $this->assertCount(10, $result);
        foreach ($result as $row) {
                        $discussionid = array_shift($discussionids);
            $this->assertEquals($discussionid, $row->discussion);
        }
    }

    public function test_count_discussion_replies_limited_sorted() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
                $result = forum_count_discussion_replies($forum->id, "d.id asc", 20);
        $this->assertCount(10, $result);
        foreach ($result as $row) {
                        $discussionid = array_shift($discussionids);
            $this->assertEquals($discussionid, $row->discussion);
        }
    }

    public function test_count_discussion_replies_paginated_sorted_small() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
                $result = forum_count_discussion_replies($forum->id, "d.id asc", -1, 0, 5);
        $this->assertCount(5, $result);
        foreach ($result as $row) {
                        $discussionid = array_shift($discussionids);
            $this->assertEquals($discussionid, $row->discussion);
        }
    }

    public function test_count_discussion_replies_paginated_sorted_small_reverse() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
                $result = forum_count_discussion_replies($forum->id, "d.id desc", -1, 0, 5);
        $this->assertCount(5, $result);
        foreach ($result as $row) {
                        $discussionid = array_pop($discussionids);
            $this->assertEquals($discussionid, $row->discussion);
        }
    }

    public function test_count_discussion_replies_limited_sorted_small_reverse() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
                $result = forum_count_discussion_replies($forum->id, "d.id desc", 5);
        $this->assertCount(5, $result);
        foreach ($result as $row) {
                        $discussionid = array_pop($discussionids);
            $this->assertEquals($discussionid, $row->discussion);
        }
    }
    public function test_discussion_pinned_sort() {
        list($forum, $discussionids) = $this->create_multiple_discussions_with_replies(10, 5);
        $cm = get_coursemodule_from_instance('forum', $forum->id);
        $discussions = forum_get_discussions($cm);
                $first = reset($discussions);
        $this->assertEquals(1, $first->pinned, "First discussion should be pinned discussion");
    }
    public function test_forum_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id),
                                                            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($forum->cmid);
        $cm = get_coursemodule_from_instance('forum', $forum->id);

                $sink = $this->redirectEvents();

        $this->setAdminUser();
        forum_view($forum, $course, $cm, $context);

        $events = $sink->get_events();
                $this->assertCount(3, $events);
        $event = array_pop($events);

                $this->assertInstanceOf('\mod_forum\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $url = new \moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }

    
    public function test_forum_discussion_view() {
        global $CFG, $USER;

        $this->resetAfterTest();

                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $discussion = $this->create_single_discussion_with_replies($forum, $USER, 2);

        $context = context_module::instance($forum->cmid);
        $cm = get_coursemodule_from_instance('forum', $forum->id);

                $sink = $this->redirectEvents();

        $this->setAdminUser();
        forum_discussion_view($context, $forum, $discussion);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = array_pop($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'view discussion', "discuss.php?d={$discussion->id}",
            $discussion->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());

    }

    
    protected function create_multiple_discussions_with_replies($discussioncount, $replycount) {
        $this->resetAfterTest();

                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $forum = $this->getDataGenerator()->create_module('forum', $record);

                $discussionids = array();
        for ($i = 0; $i < $discussioncount; $i++) {
                        if ($i == 3) {
                $discussion = $this->create_single_discussion_pinned_with_replies($forum, $user, $replycount);
            } else {
                $discussion = $this->create_single_discussion_with_replies($forum, $user, $replycount);
            }

            $discussionids[] = $discussion->id;
        }
        return array($forum, $discussionids);
    }

    
    protected function create_single_discussion_with_replies($forum, $user, $replycount) {
        global $DB;

        $generator = self::getDataGenerator()->get_plugin_generator('mod_forum');

        $record = new stdClass();
        $record->course = $forum->course;
        $record->forum = $forum->id;
        $record->userid = $user->id;
        $discussion = $generator->create_discussion($record);

                $replyto = $DB->get_record('forum_posts', array('discussion' => $discussion->id));

                $post = new stdClass();
        $post->userid = $user->id;
        $post->discussion = $discussion->id;
        $post->parent = $replyto->id;

        for ($i = 0; $i < $replycount; $i++) {
            $generator->create_post($post);
        }

        return $discussion;
    }
    
    protected function create_single_discussion_pinned_with_replies($forum, $user, $replycount) {
        global $DB;

        $generator = self::getDataGenerator()->get_plugin_generator('mod_forum');

        $record = new stdClass();
        $record->course = $forum->course;
        $record->forum = $forum->id;
        $record->userid = $user->id;
        $record->pinned = FORUM_DISCUSSION_PINNED;
        $discussion = $generator->create_discussion($record);

                $replyto = $DB->get_record('forum_posts', array('discussion' => $discussion->id));

                $post = new stdClass();
        $post->userid = $user->id;
        $post->discussion = $discussion->id;
        $post->parent = $replyto->id;

        for ($i = 0; $i < $replycount; $i++) {
            $generator->create_post($post);
        }

        return $discussion;
    }

    
    public function test_mod_forum_rating_can_see_item_ratings() {
        global $DB;

        $this->resetAfterTest();

                $course = new stdClass();
        $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = true;
        $course = $this->getDataGenerator()->create_course($course);
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $generator = self::getDataGenerator()->get_plugin_generator('mod_forum');
        $cm = get_coursemodule_from_instance('forum', $forum->id);
        $context = context_module::instance($cm->id);

                $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $user4 = $this->getDataGenerator()->create_user();

                $role = $DB->get_record('role', array('shortname' => 'teacher'), '*', MUST_EXIST);
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, $role->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, $role->id);
        $this->getDataGenerator()->enrol_user($user3->id, $course->id, $role->id);
        $this->getDataGenerator()->enrol_user($user4->id, $course->id, $role->id);

        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        groups_add_member($group1, $user1);
        groups_add_member($group1, $user2);
        groups_add_member($group2, $user3);
        groups_add_member($group2, $user4);

        $record = new stdClass();
        $record->course = $forum->course;
        $record->forum = $forum->id;
        $record->userid = $user1->id;
        $record->groupid = $group1->id;
        $discussion = $generator->create_discussion($record);

                $post = $DB->get_record('forum_posts', array('discussion' => $discussion->id));

        $ratingoptions = new stdClass;
        $ratingoptions->context = $context;
        $ratingoptions->ratingarea = 'post';
        $ratingoptions->component = 'mod_forum';
        $ratingoptions->itemid  = $post->id;
        $ratingoptions->scaleid = 2;
        $ratingoptions->userid  = $user2->id;
        $rating = new rating($ratingoptions);
        $rating->update_rating(2);

                unassign_capability('moodle/site:accessallgroups', $role->id);
        $params = array('contextid' => 2,
                        'component' => 'mod_forum',
                        'ratingarea' => 'post',
                        'itemid' => $post->id,
                        'scaleid' => 2);
        $this->setUser($user1);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user2);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user3);
        $this->assertFalse(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user4);
        $this->assertFalse(mod_forum_rating_can_see_item_ratings($params));

                assign_capability('moodle/site:accessallgroups', CAP_ALLOW, $role->id, $context->id);
        $this->setUser($user1);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user2);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user3);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user4);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));

                $course->groupmode = VISIBLEGROUPS;
        $DB->update_record('course', $course);
        unassign_capability('moodle/site:accessallgroups', $role->id);
        $this->setUser($user1);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user2);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user3);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));
        $this->setUser($user4);
        $this->assertTrue(mod_forum_rating_can_see_item_ratings($params));

    }

    
    public function test_forum_get_discussions_with_groups() {
        global $DB;

        $this->resetAfterTest(true);

                $course = self::getDataGenerator()->create_course(array('groupmode' => VISIBLEGROUPS, 'groupmodeforce' => 0));
        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();

        $role = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
        self::getDataGenerator()->enrol_user($user1->id, $course->id, $role->id);
        self::getDataGenerator()->enrol_user($user2->id, $course->id, $role->id);
        self::getDataGenerator()->enrol_user($user3->id, $course->id, $role->id);

                $record = new stdClass();
        $record->course = $course->id;
        $forum = self::getDataGenerator()->create_module('forum', $record, array('groupmode' => SEPARATEGROUPS));
        $cm = get_coursemodule_from_instance('forum', $forum->id);

                $group1 = self::getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = self::getDataGenerator()->create_group(array('courseid' => $course->id));
        $group3 = self::getDataGenerator()->create_group(array('courseid' => $course->id));

                groups_add_member($group1->id, $user1->id);
        groups_add_member($group2->id, $user1->id);

                groups_add_member($group1->id, $user2->id);
        groups_add_member($group3->id, $user3->id);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user1->id;
        $record['groupid'] = $group1->id;
        $discussiong1u1 = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $record['groupid'] = $group2->id;
        $discussiong2u1 = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $record['userid'] = $user2->id;
        $record['groupid'] = $group1->id;
        $discussiong1u2 = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $record['userid'] = $user3->id;
        $record['groupid'] = $group3->id;
        $discussiong3u3 = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        self::setUser($user1);
                $discussions = forum_get_discussions($cm);
        self::assertCount(2, $discussions);
        foreach ($discussions as $discussion) {
            self::assertEquals($group1->id, $discussion->groupid);
        }

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, 0);
        self::assertCount(3, $discussions);

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, $group1->id);
        self::assertCount(2, $discussions);
        foreach ($discussions as $discussion) {
            self::assertEquals($group1->id, $discussion->groupid);
        }

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, $group2->id);
        self::assertCount(1, $discussions);
        $discussion = array_shift($discussions);
        self::assertEquals($group2->id, $discussion->groupid);
        self::assertEquals($user1->id, $discussion->userid);
        self::assertEquals($discussiong2u1->id, $discussion->discussion);

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, $group3->id);
        self::assertCount(0, $discussions);

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, $group3->id + 1000);
        self::assertCount(0, $discussions);

        self::setUser($user2);

                $discussions = forum_get_discussions($cm);
        self::assertCount(2, $discussions);
        foreach ($discussions as $discussion) {
            self::assertEquals($group1->id, $discussion->groupid);
        }

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, 0);
        self::assertCount(2, $discussions);
        foreach ($discussions as $discussion) {
            self::assertEquals($group1->id, $discussion->groupid);
        }

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, $group2->id);
        self::assertCount(0, $discussions);

                $discussions = forum_get_discussions($cm, '', true, -1, -1, false, -1, 0, $group3->id);
        self::assertCount(0, $discussions);

    }

    
    public function test_forum_user_can_post_discussion() {
        global $CFG, $DB;

        $this->resetAfterTest(true);

                $course = self::getDataGenerator()->create_course(array('groupmode' => SEPARATEGROUPS, 'groupmodeforce' => 1));
        $user = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

                $record = new stdClass();
        $record->course = $course->id;
        $forum = self::getDataGenerator()->create_module('forum', $record, array('groupmode' => SEPARATEGROUPS));
        $cm = get_coursemodule_from_instance('forum', $forum->id);
        $context = context_module::instance($cm->id);

        self::setUser($user);

                $can = forum_user_can_post_discussion($forum, null, -1, $cm, $context);
        $this->assertFalse($can);

                $group = $this->getDataGenerator()->create_group(array('courseid' => $course->id));

                $can = forum_user_can_post_discussion($forum, $group->id, -1, $cm, $context);
        $this->assertFalse($can);

                groups_add_member($group->id, $user->id);

                $can = forum_user_can_post_discussion($forum, $group->id + 1, -1, $cm, $context);
        $this->assertFalse($can);

                $can = forum_user_can_post_discussion($forum, null, -1, $cm, $context);
        $this->assertTrue($can);

        $can = forum_user_can_post_discussion($forum, $group->id, -1, $cm, $context);
        $this->assertTrue($can);

                $can = forum_user_can_post_discussion($forum, -1, -1, $cm, $context);
        $this->assertFalse($can);

        $this->setAdminUser();
        $can = forum_user_can_post_discussion($forum, -1, -1, $cm, $context);
        $this->assertTrue($can);

                $forum->type = 'news';
        $DB->update_record('forum', $forum);

                $can = forum_user_can_post_discussion($forum, null, -1, $cm, $context);
        $this->assertTrue($can);

                self::setUser($user);
        $can = forum_user_can_post_discussion($forum, null, -1, $cm, $context);
        $this->assertFalse($can);

                $forum->type = 'eachuser';
        $DB->update_record('forum', $forum);

                $can = forum_user_can_post_discussion($forum, null, -1, $cm, $context);
        $this->assertTrue($can);

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        $record->groupid = $group->id;
        $discussion = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $can = forum_user_can_post_discussion($forum, null, -1, $cm, $context);
        $this->assertFalse($can);

                $course->groupmode = NOGROUPS;
        $course->groupmodeforce = 0;
        $DB->update_record('course', $course);

        $forum->type = 'general';
        $forum->groupmode = NOGROUPS;
        $DB->update_record('forum', $forum);

        $can = forum_user_can_post_discussion($forum, null, -1, $cm, $context);
        $this->assertTrue($can);
    }

    
    public function test_forum_user_has_posted_discussion_no_groups() {
        global $CFG;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $author = self::getDataGenerator()->create_user();
        $other = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course->id);
        $forum = self::getDataGenerator()->create_module('forum', (object) ['course' => $course->id ]);

        self::setUser($author);

                $this->assertFalse(forum_user_has_posted_discussion($forum->id, $author->id));
        $this->assertFalse(forum_user_has_posted_discussion($forum->id, $other->id));

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $author->id;
        $record->forum = $forum->id;
        $discussion = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $this->assertTrue(forum_user_has_posted_discussion($forum->id, $author->id));
        $this->assertFalse(forum_user_has_posted_discussion($forum->id, $other->id));
    }

    
    public function test_forum_user_has_posted_discussion_multiple_forums() {
        global $CFG;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $author = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course->id);
        $forum1 = self::getDataGenerator()->create_module('forum', (object) ['course' => $course->id ]);
        $forum2 = self::getDataGenerator()->create_module('forum', (object) ['course' => $course->id ]);

        self::setUser($author);

                $this->assertFalse(forum_user_has_posted_discussion($forum1->id, $author->id));
        $this->assertFalse(forum_user_has_posted_discussion($forum2->id, $author->id));

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $author->id;
        $record->forum = $forum1->id;
        $discussion = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $this->assertTrue(forum_user_has_posted_discussion($forum1->id, $author->id));
        $this->assertFalse(forum_user_has_posted_discussion($forum2->id, $author->id));
    }

    
    public function test_forum_user_has_posted_discussion_multiple_groups() {
        global $CFG;

        $this->resetAfterTest(true);

        $course = self::getDataGenerator()->create_course();
        $author = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course->id);

        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course->id));
        groups_add_member($group1->id, $author->id);
        groups_add_member($group2->id, $author->id);

        $forum = self::getDataGenerator()->create_module('forum', (object) ['course' => $course->id ], [
                    'groupmode' => SEPARATEGROUPS,
                ]);

        self::setUser($author);

                $this->assertFalse(forum_user_has_posted_discussion($forum->id, $author->id));
        $this->assertFalse(forum_user_has_posted_discussion($forum->id, $author->id, $group1->id));
        $this->assertFalse(forum_user_has_posted_discussion($forum->id, $author->id, $group2->id));

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $author->id;
        $record->forum = $forum->id;
        $record->groupid = $group1->id;
        $discussion = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $this->assertTrue(forum_user_has_posted_discussion($forum->id, $author->id));
        $this->assertTrue(forum_user_has_posted_discussion($forum->id, $author->id, $group1->id));
        $this->assertFalse(forum_user_has_posted_discussion($forum->id, $author->id, $group2->id));

                $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $author->id;
        $record->forum = $forum->id;
        $record->groupid = $group2->id;
        $discussion = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $this->assertTrue(forum_user_has_posted_discussion($forum->id, $author->id));
        $this->assertTrue(forum_user_has_posted_discussion($forum->id, $author->id, $group1->id));
        $this->assertTrue(forum_user_has_posted_discussion($forum->id, $author->id, $group2->id));
    }

    
    public function test_mod_forum_myprofile_navigation() {
        $this->resetAfterTest(true);

                $tree = new \core_user\output\myprofile\tree();
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $iscurrentuser = true;

                $this->setUser($user);

                mod_forum_myprofile_navigation($tree, $user, $iscurrentuser, $course);
        $reflector = new ReflectionObject($tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('forumposts', $nodes->getValue($tree));
        $this->assertArrayHasKey('forumdiscussions', $nodes->getValue($tree));
    }

    
    public function test_mod_forum_myprofile_navigation_as_guest() {
        global $USER;

        $this->resetAfterTest(true);

                $tree = new \core_user\output\myprofile\tree();
        $course = $this->getDataGenerator()->create_course();
        $iscurrentuser = true;

                $this->setGuestUser();

                mod_forum_myprofile_navigation($tree, $USER, $iscurrentuser, $course);
        $reflector = new ReflectionObject($tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayNotHasKey('forumposts', $nodes->getValue($tree));
        $this->assertArrayNotHasKey('forumdiscussions', $nodes->getValue($tree));
    }

    
    public function test_mod_forum_myprofile_navigation_different_user() {
        $this->resetAfterTest(true);

                $tree = new \core_user\output\myprofile\tree();
        $user = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $iscurrentuser = true;

                $this->setUser($user2);

                mod_forum_myprofile_navigation($tree, $user, $iscurrentuser, $course);
        $reflector = new ReflectionObject($tree);
        $nodes = $reflector->getProperty('nodes');
        $nodes->setAccessible(true);
        $this->assertArrayHasKey('forumposts', $nodes->getValue($tree));
        $this->assertArrayHasKey('forumdiscussions', $nodes->getValue($tree));
    }

    public function test_print_overview() {
        $this->resetAfterTest();
        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

                $author = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course1->id);
        $this->getDataGenerator()->enrol_user($author->id, $course2->id);

                $viewer = self::getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer->id, $course1->id);
        $this->getDataGenerator()->enrol_user($viewer->id, $course2->id);

                $record = new stdClass();
        $record->course = $course1->id;
        $forum1 = self::getDataGenerator()->create_module('forum', (object) array('course' => $course1->id));
        $forum2 = self::getDataGenerator()->create_module('forum', (object) array('course' => $course2->id));

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $author->id;
        $record->forum = $forum1->id;
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $this->setUser($viewer->id);
        $courses = array(
            $course1->id => clone $course1,
            $course2->id => clone $course2,
        );

        foreach ($courses as $courseid => $course) {
            $courses[$courseid]->lastaccess = 0;
        }
        $results = array();
        forum_print_overview($courses, $results);

                $this->assertCount(1, $results);

                $this->assertCount(1, $results[$course1->id]);
        $this->assertArrayHasKey('forum', $results[$course1->id]);
    }

    public function test_print_overview_groups() {
        $this->resetAfterTest();
        $course1 = self::getDataGenerator()->create_course();
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));

                $author = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course1->id);

                $viewer1 = self::getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer1->id, $course1->id);
        $this->getDataGenerator()->create_group_member(array('userid' => $viewer1->id, 'groupid' => $group1->id));

        $viewer2 = self::getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer2->id, $course1->id);
        $this->getDataGenerator()->create_group_member(array('userid' => $viewer2->id, 'groupid' => $group2->id));

                $record = new stdClass();
        $record->course = $course1->id;
        $forum1 = self::getDataGenerator()->create_module('forum', (object) array(
            'course'        => $course1->id,
            'groupmode'     => SEPARATEGROUPS,
        ));

                $record = new stdClass();
        $record->course     = $course1->id;
        $record->userid     = $author->id;
        $record->forum      = $forum1->id;
        $record->groupid    = $group1->id;
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $course1->lastaccess = 0;
        $courses = array($course1->id => $course1);

                $this->setUser($viewer1->id);
        $results = array();
        forum_print_overview($courses, $results);

                $this->assertCount(1, $results);

                $this->assertCount(1, $results[$course1->id]);
        $this->assertArrayHasKey('forum', $results[$course1->id]);

        $this->setUser($viewer2->id);
        $results = array();
        forum_print_overview($courses, $results);

                $this->assertCount(0, $results);
    }

    
    public function test_print_overview_timed($config, $hasresult) {
        $this->resetAfterTest();
        $course1 = self::getDataGenerator()->create_course();

                $author = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course1->id);

                $viewer = self::getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer->id, $course1->id);

                $record = new stdClass();
        $record->course = $course1->id;
        $forum1 = self::getDataGenerator()->create_module('forum', (object) array('course' => $course1->id));

                $record = new stdClass();
        $record->course = $course1->id;
        $record->userid = $author->id;
        $record->forum = $forum1->id;
        if (isset($config['timestartmodifier'])) {
            $record->timestart = time() + $config['timestartmodifier'];
        }
        if (isset($config['timeendmodifier'])) {
            $record->timeend = time() + $config['timeendmodifier'];
        }
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $course1->lastaccess = 0;
        $courses = array($course1->id => $course1);

                $this->setUser($viewer->id);
        $results = array();
        forum_print_overview($courses, $results);

        if ($hasresult) {
                        $this->assertCount(1, $results);

                        $this->assertCount(1, $results[$course1->id]);
            $this->assertArrayHasKey('forum', $results[$course1->id]);
        } else {
                        $this->assertCount(0, $results);
        }
    }

    
    public function test_print_overview_timed_groups($config, $hasresult) {
        $this->resetAfterTest();
        $course1 = self::getDataGenerator()->create_course();
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));
        $group2 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));

                $author = self::getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course1->id);

                $viewer1 = self::getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer1->id, $course1->id);
        $this->getDataGenerator()->create_group_member(array('userid' => $viewer1->id, 'groupid' => $group1->id));

        $viewer2 = self::getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer2->id, $course1->id);
        $this->getDataGenerator()->create_group_member(array('userid' => $viewer2->id, 'groupid' => $group2->id));

                $record = new stdClass();
        $record->course = $course1->id;
        $forum1 = self::getDataGenerator()->create_module('forum', (object) array(
            'course'        => $course1->id,
            'groupmode'     => SEPARATEGROUPS,
        ));

                $record = new stdClass();
        $record->course     = $course1->id;
        $record->userid     = $author->id;
        $record->forum      = $forum1->id;
        $record->groupid    = $group1->id;
        if (isset($config['timestartmodifier'])) {
            $record->timestart = time() + $config['timestartmodifier'];
        }
        if (isset($config['timeendmodifier'])) {
            $record->timeend = time() + $config['timeendmodifier'];
        }
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $course1->lastaccess = 0;
        $courses = array($course1->id => $course1);

                $this->setUser($viewer1->id);
        $results = array();
        forum_print_overview($courses, $results);

        if ($hasresult) {
                        $this->assertCount(1, $results);

                        $this->assertCount(1, $results[$course1->id]);
            $this->assertArrayHasKey('forum', $results[$course1->id]);
        } else {
                        $this->assertCount(0, $results);
        }

        $this->setUser($viewer2->id);
        $results = array();
        forum_print_overview($courses, $results);

                $this->assertCount(0, $results);
    }

    public function print_overview_timed_provider() {
        return array(
            'timestart_past' => array(
                'discussionconfig' => array(
                    'timestartmodifier' => -86000,
                ),
                'hasresult'         => true,
            ),
            'timestart_future' => array(
                'discussionconfig' => array(
                    'timestartmodifier' => 86000,
                ),
                'hasresult'         => false,
            ),
            'timeend_past' => array(
                'discussionconfig' => array(
                    'timeendmodifier'   => -86000,
                ),
                'hasresult'         => false,
            ),
            'timeend_future' => array(
                'discussionconfig' => array(
                    'timeendmodifier'   => 86000,
                ),
                'hasresult'         => true,
            ),
        );
    }

    
    public function test_pinned_discussion_with_group() {
        global $SESSION;

        $this->resetAfterTest();
        $course1 = $this->getDataGenerator()->create_course();
        $group1 = $this->getDataGenerator()->create_group(array('courseid' => $course1->id));

                $author = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($author->id, $course1->id);

                $viewer1 = $this->getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer1->id, $course1->id);

        $viewer2 = $this->getDataGenerator()->create_user((object) array('trackforums' => 1));
        $this->getDataGenerator()->enrol_user($viewer2->id, $course1->id);
        $this->getDataGenerator()->create_group_member(array('userid' => $viewer2->id, 'groupid' => $group1->id));

        $forum1 = $this->getDataGenerator()->create_module('forum', (object) array(
            'course' => $course1->id,
            'groupmode' => SEPARATEGROUPS,
        ));

        $coursemodule = get_coursemodule_from_instance('forum', $forum1->id);

        $alldiscussions = array();
        $group1discussions = array();

                        $allrecord = new stdClass();
        $allrecord->course = $course1->id;
        $allrecord->userid = $author->id;
        $allrecord->forum = $forum1->id;
        $allrecord->pinned = FORUM_DISCUSSION_PINNED;

        $group1record = new stdClass();
        $group1record->course = $course1->id;
        $group1record->userid = $author->id;
        $group1record->forum = $forum1->id;
        $group1record->groupid = $group1->id;
        $group1record->pinned = FORUM_DISCUSSION_PINNED;

        $alldiscussions[] = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($allrecord);
        $group1discussions[] = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($group1record);

                $allrecord->pinned = FORUM_DISCUSSION_UNPINNED;
        $group1record->pinned = FORUM_DISCUSSION_UNPINNED;
        for ($i = 0; $i < 3; $i++) {
            $alldiscussions[] = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($allrecord);
            $group1discussions[] = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($group1record);
        }

                                $this->setUser($viewer1->id);

                        $neighbours = forum_get_discussion_neighbours($coursemodule, $alldiscussions[3], $forum1);
                $this->assertEquals($alldiscussions[2]->id, $neighbours['prev']->id);
                $this->assertEquals($alldiscussions[0]->id, $neighbours['next']->id);

                        $neighbours = forum_get_discussion_neighbours($coursemodule, $alldiscussions[0], $forum1);
                $this->assertEquals($alldiscussions[3]->id, $neighbours['prev']->id);
                $this->assertEmpty($neighbours['next']);

                        $neighbours = forum_get_discussion_neighbours($coursemodule, $alldiscussions[1], $forum1);
                $this->assertEmpty($neighbours['prev']);
                $this->assertEquals($alldiscussions[2]->id, $neighbours['next']->id);

                $SESSION->currentgroup = null;

                                $this->setUser($viewer2->id);

                        $neighbours = forum_get_discussion_neighbours($coursemodule, $alldiscussions[1], $forum1);
                $this->assertEmpty($neighbours['prev']);
                $this->assertEquals($group1discussions[1]->id, $neighbours['next']->id);

                        $neighbours = forum_get_discussion_neighbours($coursemodule, $alldiscussions[3], $forum1);
                $this->assertEquals($group1discussions[2]->id, $neighbours['prev']->id);
                $this->assertEquals($group1discussions[3]->id, $neighbours['next']->id);

                        $neighbours = forum_get_discussion_neighbours($coursemodule, $group1discussions[3], $forum1);
                $this->assertEquals($alldiscussions[3]->id, $neighbours['prev']->id);
                $this->assertEquals($alldiscussions[0]->id, $neighbours['next']->id);

                        $neighbours = forum_get_discussion_neighbours($coursemodule, $group1discussions[0], $forum1);
                $this->assertEquals($alldiscussions[0]->id, $neighbours['prev']->id);
                $this->assertEmpty($neighbours['next']);
    }

    
    public function test_pinned_with_timed_discussions() {
        global $CFG;

        $CFG->forum_enabletimedposts = true;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

                $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

                $record = new stdClass();
        $record->course = $course->id;
        $forum = $this->getDataGenerator()->create_module('forum', (object) array(
            'course' => $course->id,
            'groupmode' => SEPARATEGROUPS,
        ));

        $coursemodule = get_coursemodule_from_instance('forum', $forum->id);
        $now = time();
        $discussions = array();
        $discussiongenerator = $this->getDataGenerator()->get_plugin_generator('mod_forum');

        $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        $record->pinned = FORUM_DISCUSSION_PINNED;
        $record->timemodified = $now;

        $discussions[] = $discussiongenerator->create_discussion($record);

        $record->pinned = FORUM_DISCUSSION_UNPINNED;
        $record->timestart = $now + 10;

        $discussions[] = $discussiongenerator->create_discussion($record);

        $record->timestart = $now;

        $discussions[] = $discussiongenerator->create_discussion($record);

                        $this->setUser($user->id);

                $neighbours = forum_get_discussion_neighbours($coursemodule, $discussions[2], $forum);
                $this->assertEmpty($neighbours['prev']);
                $this->assertEquals($discussions[1]->id, $neighbours['next']->id);

                $neighbours = forum_get_discussion_neighbours($coursemodule, $discussions[1], $forum);
                $this->assertEquals($discussions[2]->id, $neighbours['prev']->id);
                $this->assertEquals($discussions[0]->id, $neighbours['next']->id);

                $neighbours = forum_get_discussion_neighbours($coursemodule, $discussions[0], $forum);
                $this->assertEquals($discussions[1]->id, $neighbours['prev']->id);
                $this->assertEmpty($neighbours['next']);
    }

    
    public function test_pinned_timed_discussions_with_timed_discussions() {
        global $CFG;

        $CFG->forum_enabletimedposts = true;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

                $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

                $record = new stdClass();
        $record->course = $course->id;
        $forum = $this->getDataGenerator()->create_module('forum', (object) array(
            'course' => $course->id,
            'groupmode' => SEPARATEGROUPS,
        ));

        $coursemodule = get_coursemodule_from_instance('forum', $forum->id);
        $now = time();
        $discussions = array();
        $discussiongenerator = $this->getDataGenerator()->get_plugin_generator('mod_forum');

        $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        $record->pinned = FORUM_DISCUSSION_PINNED;
        $record->timemodified = $now;
        $record->timestart = $now + 10;

        $discussions[] = $discussiongenerator->create_discussion($record);

        $record->pinned = FORUM_DISCUSSION_UNPINNED;

        $discussions[] = $discussiongenerator->create_discussion($record);

        $record->timestart = $now;

        $discussions[] = $discussiongenerator->create_discussion($record);

        $record->pinned = FORUM_DISCUSSION_PINNED;

        $discussions[] = $discussiongenerator->create_discussion($record);

                        $this->setUser($user->id);

                $neighbours = forum_get_discussion_neighbours($coursemodule, $discussions[2], $forum);
                $this->assertEmpty($neighbours['prev']);
                $this->assertEquals($discussions[1]->id, $neighbours['next']->id);

                $neighbours = forum_get_discussion_neighbours($coursemodule, $discussions[1], $forum);
                $this->assertEquals($discussions[2]->id, $neighbours['prev']->id);
                $this->assertEquals($discussions[3]->id, $neighbours['next']->id);

                $neighbours = forum_get_discussion_neighbours($coursemodule, $discussions[3], $forum);
                $this->assertEquals($discussions[1]->id, $neighbours['prev']->id);
                $this->assertEquals($discussions[0]->id, $neighbours['next']->id);

                $neighbours = forum_get_discussion_neighbours($coursemodule, $discussions[0], $forum);
                $this->assertEquals($discussions[3]->id, $neighbours['prev']->id);
                $this->assertEmpty($neighbours['next']);
    }

    
    public function test_forum_get_unmailed_posts($discussiondata, $enabletimedposts, $expectedcount, $expectedreplycount) {
        global $CFG, $DB;

        $this->resetAfterTest();

                $CFG->forum_enabletimedposts = $enabletimedposts;

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $user = $this->getDataGenerator()->create_user();
        $forumgen = $this->getDataGenerator()->get_plugin_generator('mod_forum');

                $time = time();

        $record = new stdClass();
        $record->course = $course->id;
        $record->userid = $user->id;
        $record->forum = $forum->id;
        if (isset($discussiondata['timecreated'])) {
            $record->timemodified = $time + $discussiondata['timecreated'];
        }
        if (isset($discussiondata['timestart'])) {
            $record->timestart = $time + $discussiondata['timestart'];
        }
        if (isset($discussiondata['timeend'])) {
            $record->timeend = $time + $discussiondata['timeend'];
        }
        if (isset($discussiondata['mailed'])) {
            $record->mailed = $discussiondata['mailed'];
        }

        $discussion = $forumgen->create_discussion($record);

                $timenow   = $time;
        $endtime   = $timenow - $CFG->maxeditingtime;
        $starttime = $endtime - 2 * DAYSECS;

        $unmailed = forum_get_unmailed_posts($starttime, $endtime, $timenow);
        $this->assertCount($expectedcount, $unmailed);

                $replyto = $DB->get_record('forum_posts', array('discussion' => $discussion->id));
        $reply = new stdClass();
        $reply->userid = $user->id;
        $reply->discussion = $discussion->id;
        $reply->parent = $replyto->id;
        $reply->created = max($replyto->created, $endtime - 1);
        $forumgen->create_post($reply);

        $unmailed = forum_get_unmailed_posts($starttime, $endtime, $timenow);
        $this->assertCount($expectedreplycount, $unmailed);
    }

    public function forum_get_unmailed_posts_provider() {
        return [
            'Untimed discussion; Single post; maxeditingtime not expired' => [
                'discussion'        => [
                ],
                'timedposts'        => false,
                'postcount'         => 0,
                'replycount'        => 0,
            ],
            'Untimed discussion; Single post; maxeditingtime expired' => [
                'discussion'        => [
                    'timecreated'   => - DAYSECS,
                ],
                'timedposts'        => false,
                'postcount'         => 1,
                'replycount'        => 2,
            ],
            'Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime not expired' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => 0,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 0,
            ],
            'Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime expired' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => - DAYSECS,
                ],
                'timedposts'        => true,
                'postcount'         => 1,
                'replycount'        => 2,
            ],
            'Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime expired; timeend not reached' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => - DAYSECS,
                    'timeend'       => + DAYSECS
                ],
                'timedposts'        => true,
                'postcount'         => 1,
                'replycount'        => 2,
            ],
            'Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime expired; timeend passed' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => - DAYSECS,
                    'timeend'       => - HOURSECS,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 0,
            ],
            'Timed discussion; Single post; Posted 1 week ago; timeend not reached' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timeend'       => + DAYSECS
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 1,
            ],
            'Timed discussion; Single post; Posted 1 week ago; timeend passed' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timeend'       => - DAYSECS,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 0,
            ],

            'Previously mailed; Untimed discussion; Single post; maxeditingtime not expired' => [
                'discussion'        => [
                    'mailed'        => 1,
                ],
                'timedposts'        => false,
                'postcount'         => 0,
                'replycount'        => 0,
            ],

            'Previously mailed; Untimed discussion; Single post; maxeditingtime expired' => [
                'discussion'        => [
                    'timecreated'   => - DAYSECS,
                    'mailed'        => 1,
                ],
                'timedposts'        => false,
                'postcount'         => 0,
                'replycount'        => 1,
            ],
            'Previously mailed; Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime not expired' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => 0,
                    'mailed'        => 1,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 0,
            ],
            'Previously mailed; Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime expired' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => - DAYSECS,
                    'mailed'        => 1,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 1,
            ],
            'Previously mailed; Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime expired; timeend not reached' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => - DAYSECS,
                    'timeend'       => + DAYSECS,
                    'mailed'        => 1,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 1,
            ],
            'Previously mailed; Timed discussion; Single post; Posted 1 week ago; timestart maxeditingtime expired; timeend passed' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timestart'     => - DAYSECS,
                    'timeend'       => - HOURSECS,
                    'mailed'        => 1,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 0,
            ],
            'Previously mailed; Timed discussion; Single post; Posted 1 week ago; timeend not reached' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timeend'       => + DAYSECS,
                    'mailed'        => 1,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 1,
            ],
            'Previously mailed; Timed discussion; Single post; Posted 1 week ago; timeend passed' => [
                'discussion'        => [
                    'timecreated'   => - WEEKSECS,
                    'timeend'       => - DAYSECS,
                    'mailed'        => 1,
                ],
                'timedposts'        => true,
                'postcount'         => 0,
                'replycount'        => 0,
            ],
        ];
    }
}
