<?php



defined('MOODLE_INTERNAL') || die();



class mod_forum_generator_testcase extends advanced_testcase {

    public function setUp() {
                        \mod_forum\subscriptions::reset_forum_cache();
    }

    public function tearDown() {
                        \mod_forum\subscriptions::reset_forum_cache();
    }

    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('forum'));

        $course = $this->getDataGenerator()->create_course();

        
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_forum');
        $this->assertInstanceOf('mod_forum_generator', $generator);
        $this->assertEquals('forum', $generator->get_modulename());

        $generator->create_instance(array('course'=>$course->id));
        $generator->create_instance(array('course'=>$course->id));
        $forum = $generator->create_instance(array('course'=>$course->id));
        $this->assertEquals(3, $DB->count_records('forum'));

        $cm = get_coursemodule_from_instance('forum', $forum->id);
        $this->assertEquals($forum->id, $cm->instance);
        $this->assertEquals('forum', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = context_module::instance($cm->id);
        $this->assertEquals($forum->cmid, $context->instanceid);

                $forum = $generator->create_instance(array('course'=>$course->id, 'assessed'=>1, 'scale'=>100));
        $gitem = $DB->get_record('grade_items', array('courseid'=>$course->id, 'itemtype'=>'mod', 'itemmodule'=>'forum', 'iteminstance'=>$forum->id));
        $this->assertNotEmpty($gitem);
        $this->assertEquals(100, $gitem->grademax);
        $this->assertEquals(0, $gitem->grademin);
        $this->assertEquals(GRADE_TYPE_VALUE, $gitem->gradetype);
    }

    
    public function test_create_discussion() {
        global $DB;

        $this->resetAfterTest(true);

                $user = self::getDataGenerator()->create_user();

                $course = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course->id;
        $forum = self::getDataGenerator()->create_module('forum', $record);

                $record = array();
        $record['course'] = $course->id;
        $record['forum'] = $forum->id;
        $record['userid'] = $user->id;
        $record['pinned'] = FORUM_DISCUSSION_PINNED;         self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        $record['pinned'] = FORUM_DISCUSSION_UNPINNED;         self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);
        self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $this->assertEquals(3, $DB->count_records_select('forum_discussions', 'forum = :forum',
            array('forum' => $forum->id)));
    }

    
    public function test_create_post() {
        global $DB;

        $this->resetAfterTest(true);

                $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();

                $course = self::getDataGenerator()->create_course();

                $record = new stdClass();
        $record->course = $course->id;
        $forum = self::getDataGenerator()->create_module('forum', $record);

                $record->forum = $forum->id;
        $record->userid = $user1->id;
        $discussion = self::getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion($record);

                $record = new stdClass();
        $record->discussion = $discussion->id;
        $record->userid = $user2->id;
        self::getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);
        $record->userid = $user3->id;
        self::getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);
        $record->userid = $user4->id;
        self::getDataGenerator()->get_plugin_generator('mod_forum')->create_post($record);

                        $this->assertEquals(4, $DB->count_records_select('forum_posts', 'discussion = :discussion',
            array('discussion' => $discussion->id)));
    }

    public function test_create_content() {
        global $DB;

        $this->resetAfterTest(true);

                $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();
        $user3 = self::getDataGenerator()->create_user();
        $user4 = self::getDataGenerator()->create_user();

        $this->setAdminUser();

                $course = self::getDataGenerator()->create_course();
        $forum = self::getDataGenerator()->create_module('forum', array('course' => $course));

        $generator = self::getDataGenerator()->get_plugin_generator('mod_forum');
                $post1 = $generator->create_content($forum);
                $post2 = $generator->create_content($forum, array('parent' => $post1->id));
        $post3 = $generator->create_content($forum, array('discussion' => $post1->discussion));
                $post4 = $generator->create_content($forum, array('parent' => $post2->id));

        $discussionrecords = $DB->get_records('forum_discussions', array('forum' => $forum->id));
        $postrecords = $DB->get_records('forum_posts');
        $postrecords2 = $DB->get_records('forum_posts', array('discussion' => $post1->discussion));
        $this->assertEquals(1, count($discussionrecords));
        $this->assertEquals(4, count($postrecords));
        $this->assertEquals(4, count($postrecords2));
        $this->assertEquals($post1->id, $discussionrecords[$post1->discussion]->firstpost);
        $this->assertEquals($post1->id, $postrecords[$post2->id]->parent);
        $this->assertEquals($post1->id, $postrecords[$post3->id]->parent);
        $this->assertEquals($post2->id, $postrecords[$post4->id]->parent);
    }
}
