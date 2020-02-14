<?php



defined('MOODLE_INTERNAL') || die();


class mod_forum_events_testcase extends advanced_testcase {

    
    public function setUp() {
                        \mod_forum\subscriptions::reset_forum_cache();

        $this->resetAfterTest();
    }

    public function tearDown() {
                        \mod_forum\subscriptions::reset_forum_cache();
    }

    
    public function test_course_searched_searchterm_validation() {
        $course = $this->getDataGenerator()->create_course();
        $coursectx = context_course::instance($course->id);
        $params = array(
            'context' => $coursectx,
        );

        $this->setExpectedException('coding_exception', 'The \'searchterm\' value must be set in other.');
        \mod_forum\event\course_searched::create($params);
    }

    
    public function test_course_searched_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);
        $params = array(
            'context' => $context,
            'other' => array('searchterm' => 'testing'),
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_COURSE.');
        \mod_forum\event\course_searched::create($params);
    }

    
    public function test_course_searched() {

                $course = $this->getDataGenerator()->create_course();
        $coursectx = context_course::instance($course->id);
        $searchterm = 'testing123';

        $params = array(
            'context' => $coursectx,
            'other' => array('searchterm' => $searchterm),
        );

                $event = \mod_forum\event\course_searched::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                 $this->assertInstanceOf('\mod_forum\event\course_searched', $event);
        $this->assertEquals($coursectx, $event->get_context());
        $expected = array($course->id, 'forum', 'search', "search.php?id={$course->id}&amp;search={$searchterm}", $searchterm);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_discussion_created_forumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\discussion_created::create($params);
    }

    
    public function test_discussion_created_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_created::create($params);
    }

    
    public function test_discussion_created() {

                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $discussion->id,
            'other' => array('forumid' => $forum->id),
        );

                $event = \mod_forum\event\discussion_created::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_created', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'add discussion', "discuss.php?d={$discussion->id}", $discussion->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_discussion_updated_forumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\discussion_updated::create($params);
    }

    
    public function test_discussion_updated_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_updated::create($params);
    }

    
    public function test_discussion_updated() {

                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $discussion->id,
            'other' => array('forumid' => $forum->id),
        );

                $event = \mod_forum\event\discussion_updated::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_updated', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_discussion_deleted_forumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\discussion_deleted::create($params);
    }

    
    public function test_discussion_deleted_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_deleted::create($params);
    }

    
    public function test_discussion_deleted() {

                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $discussion->id,
            'other' => array('forumid' => $forum->id),
        );

        $event = \mod_forum\event\discussion_deleted::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'delete discussion', "view.php?id={$forum->cmid}", $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_discussion_moved_fromforumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $toforum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $context = context_module::instance($toforum->cmid);

        $params = array(
            'context' => $context,
            'other' => array('toforumid' => $toforum->id)
        );

        $this->setExpectedException('coding_exception', 'The \'fromforumid\' value must be set in other.');
        \mod_forum\event\discussion_moved::create($params);
    }

    
    public function test_discussion_moved_toforumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $fromforum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $toforum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($toforum->cmid);

        $params = array(
            'context' => $context,
            'other' => array('fromforumid' => $fromforum->id)
        );

        $this->setExpectedException('coding_exception', 'The \'toforumid\' value must be set in other.');
        \mod_forum\event\discussion_moved::create($params);
    }

    
    public function test_discussion_moved_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $fromforum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $toforum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $fromforum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $params = array(
            'context' => context_system::instance(),
            'objectid' => $discussion->id,
            'other' => array('fromforumid' => $fromforum->id, 'toforumid' => $toforum->id)
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_moved::create($params);
    }

    
    public function test_discussion_moved() {
                $course = $this->getDataGenerator()->create_course();
        $fromforum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $toforum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $fromforum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $context = context_module::instance($toforum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $discussion->id,
            'other' => array('fromforumid' => $fromforum->id, 'toforumid' => $toforum->id)
        );

        $event = \mod_forum\event\discussion_moved::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_moved', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'move discussion', "discuss.php?d={$discussion->id}",
            $discussion->id, $toforum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }


    
    public function test_discussion_viewed_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $params = array(
            'context' => context_system::instance(),
            'objectid' => $discussion->id,
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_viewed::create($params);
    }

    
    public function test_discussion_viewed() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $discussion->id,
        );

        $event = \mod_forum\event\discussion_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'view discussion', "discuss.php?d={$discussion->id}",
            $discussion->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_course_module_viewed_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'objectid' => $forum->id,
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\course_module_viewed::create($params);
    }

    
    public function test_course_module_viewed() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $forum->id,
        );

        $event = \mod_forum\event\course_module_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'view forum', "view.php?f={$forum->id}", $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_subscription_created_forumid_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\subscription_created::create($params);
    }

    
    public function test_subscription_created_relateduserid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $forum->id,
        );

        $this->setExpectedException('coding_exception', 'The \'relateduserid\' must be set.');
        \mod_forum\event\subscription_created::create($params);
    }

    
    public function test_subscription_created_contextlevel_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\subscription_created::create($params);
    }

    
    public function test_subscription_created() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();
        $context = context_module::instance($forum->cmid);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $subscription = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_subscription($record);

        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $event = \mod_forum\event\subscription_created::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_created', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'subscribe', "view.php?f={$forum->id}", $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/subscribers.php', array('id' => $forum->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_subscription_deleted_forumid_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\subscription_deleted::create($params);
    }

    
    public function test_subscription_deleted_relateduserid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $forum->id,
        );

        $this->setExpectedException('coding_exception', 'The \'relateduserid\' must be set.');
        \mod_forum\event\subscription_deleted::create($params);
    }

    
    public function test_subscription_deleted_contextlevel_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\subscription_deleted::create($params);
    }

    
    public function test_subscription_deleted() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();
        $context = context_module::instance($forum->cmid);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $subscription = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_subscription($record);

        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $event = \mod_forum\event\subscription_deleted::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'unsubscribe', "view.php?f={$forum->id}", $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/subscribers.php', array('id' => $forum->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_readtracking_enabled_forumid_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\readtracking_enabled::create($params);
    }

    
    public function test_readtracking_enabled_relateduserid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $forum->id,
        );

        $this->setExpectedException('coding_exception', 'The \'relateduserid\' must be set.');
        \mod_forum\event\readtracking_enabled::create($params);
    }

    
    public function test_readtracking_enabled_contextlevel_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\readtracking_enabled::create($params);
    }

    
    public function test_readtracking_enabled() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $event = \mod_forum\event\readtracking_enabled::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\readtracking_enabled', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'start tracking', "view.php?f={$forum->id}", $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_readtracking_disabled_forumid_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\readtracking_disabled::create($params);
    }

    
    public function test_readtracking_disabled_relateduserid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $forum->id,
        );

        $this->setExpectedException('coding_exception', 'The \'relateduserid\' must be set.');
        \mod_forum\event\readtracking_disabled::create($params);
    }

    
    public function test_readtracking_disabled_contextlevel_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\readtracking_disabled::create($params);
    }

    
    public function test_readtracking_disabled() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $event = \mod_forum\event\readtracking_disabled::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\readtracking_disabled', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'stop tracking', "view.php?f={$forum->id}", $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_subscribers_viewed_forumid_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\subscribers_viewed::create($params);
    }

    
    public function test_subscribers_viewed_contextlevel_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_system::instance(),
            'other' => array('forumid' => $forum->id),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\subscribers_viewed::create($params);
    }

    
    public function test_subscribers_viewed() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'other' => array('forumid' => $forum->id),
        );

        $event = \mod_forum\event\subscribers_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscribers_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'view subscribers', "subscribers.php?id={$forum->id}", $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_user_report_viewed_reportmode_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();

        $params = array(
            'context' => context_course::instance($course->id),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception', 'The \'reportmode\' value must be set in other.');
        \mod_forum\event\user_report_viewed::create($params);
    }

    
    public function test_user_report_viewed_contextlevel_validation() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'other' => array('reportmode' => 'posts'),
            'relateduserid' => $user->id,
        );

        $this->setExpectedException('coding_exception',
                'Context level must be either CONTEXT_SYSTEM, CONTEXT_COURSE or CONTEXT_USER.');
        \mod_forum\event\user_report_viewed::create($params);
    }

    
    public function test_user_report_viewed_relateduserid_validation() {

        $params = array(
            'context' => context_system::instance(),
            'other' => array('reportmode' => 'posts'),
        );

        $this->setExpectedException('coding_exception', 'The \'relateduserid\' must be set.');
        \mod_forum\event\user_report_viewed::create($params);
    }

    
    public function test_user_report_viewed() {
                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $params = array(
            'context' => $context,
            'relateduserid' => $user->id,
            'other' => array('reportmode' => 'discussions'),
        );

        $event = \mod_forum\event\user_report_viewed::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\user_report_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'user report',
            "user.php?id={$user->id}&amp;mode=discussions&amp;course={$course->id}", $user->id);
        $this->assertEventLegacyLogData($expected, $event);
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_post_created_postid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'other' => array('forumid' => $forum->id, 'forumtype' => $forum->type, 'discussionid' => $discussion->id)
        );

        \mod_forum\event\post_created::create($params);
    }

    
    public function test_post_created_discussionid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'The \'discussionid\' value must be set in other.');
        \mod_forum\event\post_created::create($params);
    }

    
    public function test_post_created_forumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\post_created::create($params);
    }

    
    public function test_post_created_forumtype_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id)
        );

        $this->setExpectedException('coding_exception', 'The \'forumtype\' value must be set in other.');
        \mod_forum\event\post_created::create($params);
    }

    
    public function test_post_created_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_system::instance(),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE');
        \mod_forum\event\post_created::create($params);
    }

    
    public function test_post_created() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $event = \mod_forum\event\post_created::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\post_created', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'add post', "discuss.php?d={$discussion->id}#p{$post->id}",
            $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/discuss.php', array('d' => $discussion->id));
        $url->set_anchor('p'.$event->objectid);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_post_created_single() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'type' => 'single'));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $event = \mod_forum\event\post_created::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\post_created', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'add post', "view.php?f={$forum->id}#p{$post->id}",
            $forum->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        $url->set_anchor('p'.$event->objectid);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_post_deleted_postid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'other' => array('forumid' => $forum->id, 'forumtype' => $forum->type, 'discussionid' => $discussion->id)
        );

        \mod_forum\event\post_deleted::create($params);
    }

    
    public function test_post_deleted_discussionid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'The \'discussionid\' value must be set in other.');
        \mod_forum\event\post_deleted::create($params);
    }

    
    public function test_post_deleted_forumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\post_deleted::create($params);
    }

    
    public function test_post_deleted_forumtype_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id)
        );

        $this->setExpectedException('coding_exception', 'The \'forumtype\' value must be set in other.');
        \mod_forum\event\post_deleted::create($params);
    }

    
    public function test_post_deleted_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_system::instance(),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE');
        \mod_forum\event\post_deleted::create($params);
    }

    
    public function test_post_deleted() {
        global $DB;

                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();
        $cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $discussionpost = $DB->get_records('forum_posts');
                $discussionpost = reset($discussionpost);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $posts = array();
        $posts[$discussionpost->id] = $discussionpost;
        for ($i = 0; $i < 3; $i++) {
            $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);
            $posts[$post->id] = $post;
        }

                $lastpost = end($posts);
        $sink = $this->redirectEvents();
        forum_delete_post($lastpost, true, $course, $cm, $forum);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\post_deleted', $event);
        $this->assertEquals(context_module::instance($forum->cmid), $event->get_context());
        $expected = array($course->id, 'forum', 'delete post', "discuss.php?d={$discussion->id}", $lastpost->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/discuss.php', array('d' => $discussion->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

                $sink = $this->redirectEvents();
        forum_delete_discussion($discussion, true, $course, $cm, $forum);
        $events = $sink->get_events();
                $this->assertCount(3, $events);

                foreach ($events as $event) {
            $post = $posts[$event->objectid];

                        $this->assertInstanceOf('\mod_forum\event\post_deleted', $event);
            $this->assertEquals(context_module::instance($forum->cmid), $event->get_context());
            $expected = array($course->id, 'forum', 'delete post', "discuss.php?d={$discussion->id}", $post->id, $forum->cmid);
            $this->assertEventLegacyLogData($expected, $event);
            $url = new \moodle_url('/mod/forum/discuss.php', array('d' => $discussion->id));
            $this->assertEquals($url, $event->get_url());
            $this->assertEventContextNotUsed($event);
            $this->assertNotEmpty($event->get_name());
        }
    }

    
    public function test_post_deleted_single() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'type' => 'single'));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $event = \mod_forum\event\post_deleted::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\post_deleted', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'delete post', "view.php?f={$forum->id}", $post->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_post_updated_discussionid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'The \'discussionid\' value must be set in other.');
        \mod_forum\event\post_updated::create($params);
    }

    
    public function test_post_updated_forumid_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\post_updated::create($params);
    }

    
    public function test_post_updated_forumtype_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id)
        );

        $this->setExpectedException('coding_exception', 'The \'forumtype\' value must be set in other.');
        \mod_forum\event\post_updated::create($params);
    }

    
    public function test_post_updated_context_validation() {
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $params = array(
            'context' => context_system::instance(),
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE');
        \mod_forum\event\post_updated::create($params);
    }

    
    public function test_post_updated() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $event = \mod_forum\event\post_updated::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\post_updated', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'update post', "discuss.php?d={$discussion->id}#p{$post->id}",
            $post->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/discuss.php', array('d' => $discussion->id));
        $url->set_anchor('p'.$event->objectid);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_post_updated_single() {
                $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', array('course' => $course->id, 'type' => 'single'));
        $user = $this->getDataGenerator()->create_user();

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $post->id,
            'other' => array('discussionid' => $discussion->id, 'forumid' => $forum->id, 'forumtype' => $forum->type)
        );

        $event = \mod_forum\event\post_updated::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\post_updated', $event);
        $this->assertEquals($context, $event->get_context());
        $expected = array($course->id, 'forum', 'update post', "view.php?f={$forum->id}#p{$post->id}",
            $post->id, $forum->cmid);
        $this->assertEventLegacyLogData($expected, $event);
        $url = new \moodle_url('/mod/forum/view.php', array('f' => $forum->id));
        $url->set_anchor('p'.$post->id);
        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);

        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_discussion_subscription_created() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $sink = $this->redirectEvents();

                \mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_created', $event);


        $cm = get_coursemodule_from_instance('forum', $discussion->forum);
        $context = \context_module::instance($cm->id);
        $this->assertEquals($context, $event->get_context());

        $url = new \moodle_url('/mod/forum/subscribe.php', array(
            'id' => $forum->id,
            'd' => $discussion->id
        ));

        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_discussion_subscription_created_validation() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'forumid' => $forum->id,
                'discussion' => $discussion->id,
            )
        );

        $event = \mod_forum\event\discussion_subscription_created::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);
    }

    
    public function test_discussion_subscription_created_validation_contextlevel() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => \context_course::instance($course->id),
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'forumid' => $forum->id,
                'discussion' => $discussion->id,
            )
        );

                $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_subscription_created::create($params);
    }

    
    public function test_discussion_subscription_created_validation_discussion() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

                $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'forumid' => $forum->id,
            )
        );

        $this->setExpectedException('coding_exception', "The 'discussion' value must be set in other.");
        \mod_forum\event\discussion_subscription_created::create($params);
    }

    
    public function test_discussion_subscription_created_validation_forumid() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

                $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'discussion' => $discussion->id,
            )
        );

        $this->setExpectedException('coding_exception', "The 'forumid' value must be set in other.");
        \mod_forum\event\discussion_subscription_created::create($params);
    }

    
    public function test_discussion_subscription_created_validation_relateduserid() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

        $context = context_module::instance($forum->cmid);

                $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $subscription->id,
            'other' => array(
                'forumid' => $forum->id,
                'discussion' => $discussion->id,
            )
        );

        $this->setExpectedException('coding_exception', "The 'relateduserid' must be set.");
        \mod_forum\event\discussion_subscription_created::create($params);
    }

    
    public function test_discussion_subscription_deleted() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $sink = $this->redirectEvents();

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_deleted', $event);


        $cm = get_coursemodule_from_instance('forum', $discussion->forum);
        $context = \context_module::instance($cm->id);
        $this->assertEquals($context, $event->get_context());

        $url = new \moodle_url('/mod/forum/subscribe.php', array(
            'id' => $forum->id,
            'd' => $discussion->id
        ));

        $this->assertEquals($url, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());
    }

    
    public function test_discussion_subscription_deleted_validation() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = \mod_forum\subscriptions::FORUM_DISCUSSION_UNSUBSCRIBED;

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => $context,
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'forumid' => $forum->id,
                'discussion' => $discussion->id,
            )
        );

        $event = \mod_forum\event\discussion_subscription_deleted::create($params);

                $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $event = reset($events);

                $params['context'] = \context_course::instance($course->id);
        $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_deleted::create($params);

                unset($params['discussion']);
        $this->setExpectedException('coding_exception', 'The \'discussion\' value must be set in other.');
        \mod_forum\event\discussion_deleted::create($params);

                unset($params['forumid']);
        $this->setExpectedException('coding_exception', 'The \'forumid\' value must be set in other.');
        \mod_forum\event\discussion_deleted::create($params);

                unset($params['relateduserid']);
        $this->setExpectedException('coding_exception', 'The \'relateduserid\' value must be set in other.');
        \mod_forum\event\discussion_deleted::create($params);
    }

    
    public function test_discussion_subscription_deleted_validation_contextlevel() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

        $context = context_module::instance($forum->cmid);

        $params = array(
            'context' => \context_course::instance($course->id),
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'forumid' => $forum->id,
                'discussion' => $discussion->id,
            )
        );

                $this->setExpectedException('coding_exception', 'Context level must be CONTEXT_MODULE.');
        \mod_forum\event\discussion_subscription_deleted::create($params);
    }

    
    public function test_discussion_subscription_deleted_validation_discussion() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

                $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'forumid' => $forum->id,
            )
        );

        $this->setExpectedException('coding_exception', "The 'discussion' value must be set in other.");
        \mod_forum\event\discussion_subscription_deleted::create($params);
    }

    
    public function test_discussion_subscription_deleted_validation_forumid() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

                $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $subscription->id,
            'relateduserid' => $user->id,
            'other' => array(
                'discussion' => $discussion->id,
            )
        );

        $this->setExpectedException('coding_exception', "The 'forumid' value must be set in other.");
        \mod_forum\event\discussion_subscription_deleted::create($params);
    }

    
    public function test_discussion_subscription_deleted_validation_relateduserid() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $subscription = new \stdClass();
        $subscription->userid  = $user->id;
        $subscription->forum = $forum->id;
        $subscription->discussion = $discussion->id;
        $subscription->preference = time();

        $subscription->id = $DB->insert_record('forum_discussion_subs', $subscription);

        $context = context_module::instance($forum->cmid);

                $params = array(
            'context' => context_module::instance($forum->cmid),
            'objectid' => $subscription->id,
            'other' => array(
                'forumid' => $forum->id,
                'discussion' => $discussion->id,
            )
        );

        $this->setExpectedException('coding_exception', "The 'relateduserid' must be set.");
        \mod_forum\event\discussion_subscription_deleted::create($params);
    }

    
    public function test_forum_subscription_page_context_valid() {
        global $CFG, $PAGE;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

                $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $options = array('course' => $course->id, 'forcesubscribe' => FORUM_CHOOSESUBSCRIBE);
        $forum = $this->getDataGenerator()->create_module('forum', $options);
        $quiz = $this->getDataGenerator()->create_module('quiz', $options);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                $PAGE = new moodle_page();
        $cm = get_coursemodule_from_instance('forum', $discussion->forum);
        $context = \context_module::instance($cm->id);
        $PAGE->set_context($context);
        $PAGE->set_cm($cm, $course, $forum);

                $sink = $this->redirectEvents();

                \mod_forum\subscriptions::subscribe_user($user->id, $forum);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_created', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::unsubscribe_user($user->id, $forum);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_deleted', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_created', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_deleted', $event);
        $this->assertEquals($context, $event->get_context());

                $PAGE = new moodle_page();
        $cm = get_coursemodule_from_instance('quiz', $quiz->id);
        $quizcontext = \context_module::instance($cm->id);
        $PAGE->set_context($quizcontext);
        $PAGE->set_cm($cm, $course, $quiz);

                $sink = $this->redirectEvents();

                \mod_forum\subscriptions::subscribe_user($user->id, $forum);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_created', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::unsubscribe_user($user->id, $forum);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_deleted', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_created', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_deleted', $event);
        $this->assertEquals($context, $event->get_context());

                $PAGE = new moodle_page();
        $coursecontext = \context_course::instance($course->id);
        $PAGE->set_context($coursecontext);

                \mod_forum\subscriptions::subscribe_user($user->id, $forum);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_created', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::unsubscribe_user($user->id, $forum);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\subscription_deleted', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::subscribe_user_to_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_created', $event);
        $this->assertEquals($context, $event->get_context());

                \mod_forum\subscriptions::unsubscribe_user_from_discussion($user->id, $discussion);

        $events = $sink->get_events();
        $sink->clear();
        $this->assertCount(1, $events);
        $event = reset($events);

                $this->assertInstanceOf('\mod_forum\event\discussion_subscription_deleted', $event);
        $this->assertEquals($context, $event->get_context());

    }

    
    public function test_observers() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/forum/lib.php');

        $forumgen = $this->getDataGenerator()->get_plugin_generator('mod_forum');

        $course = $this->getDataGenerator()->create_course();
        $trackedrecord = array('course' => $course->id, 'type' => 'general', 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $untrackedrecord = array('course' => $course->id, 'type' => 'general');
        $trackedforum = $this->getDataGenerator()->create_module('forum', $trackedrecord);
        $untrackedforum = $this->getDataGenerator()->create_module('forum', $untrackedrecord);

                        $user = $this->getDataGenerator()->create_user(array(
            'maildigest' => 1,
            'trackforums' => 1
        ));

        $manplugin = enrol_get_plugin('manual');
        $manualenrol = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
        $student = $DB->get_record('role', array('shortname' => 'student'));

                $manplugin->enrol_user($manualenrol, $user->id, $student->id);

                        set_config('forum_trackingtype', 1);
        set_config('forum_trackreadposts', 1);

        $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $trackedforum->id;
        $record['userid'] = $user->id;
        $discussion = $forumgen->create_discussion($record);

        $record = array();
        $record['discussion'] = $discussion->id;
        $record['userid'] = $user->id;
        $post = $forumgen->create_post($record);

        forum_tp_add_read_record($user->id, $post->id);
        forum_set_user_maildigest($trackedforum, 2, $user);
        forum_tp_stop_tracking($untrackedforum->id, $user->id);

        $this->assertEquals(1, $DB->count_records('forum_subscriptions'));
        $this->assertEquals(1, $DB->count_records('forum_digests'));
        $this->assertEquals(1, $DB->count_records('forum_track_prefs'));
        $this->assertEquals(1, $DB->count_records('forum_read'));

                $forumrecord = array('course' => $course->id, 'type' => 'general', 'forcesubscribe' => FORUM_INITIALSUBSCRIBE);
        $extraforum = $this->getDataGenerator()->create_module('forum', $forumrecord);
        $this->assertEquals(2, $DB->count_records('forum_subscriptions'));

        $manplugin->unenrol_user($manualenrol, $user->id);

        $this->assertEquals(0, $DB->count_records('forum_digests'));
        $this->assertEquals(0, $DB->count_records('forum_subscriptions'));
        $this->assertEquals(0, $DB->count_records('forum_track_prefs'));
        $this->assertEquals(0, $DB->count_records('forum_read'));
    }

}
