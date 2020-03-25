<?php



defined('MOODLE_INTERNAL') || die();


class mod_forum_output_email_testcase extends advanced_testcase {
    
    public function postdate_provider() {
        return array(
            'Timed discussions disabled, timestart unset' => array(
                'globalconfig'      => array(
                    'forum_enabletimedposts' => 0,
                ),
                'forumconfig'       => array(
                ),
                'postconfig'        => array(
                    'modified'  => 1000,
                ),
                'discussionconfig'  => array(
                ),
                'expectation'       => 1000,
            ),
            'Timed discussions disabled, timestart set and newer' => array(
                'globalconfig'      => array(
                    'forum_enabletimedposts' => 0,
                ),
                'forumconfig'       => array(
                ),
                'postconfig'        => array(
                    'modified'  => 1000,
                ),
                'discussionconfig'  => array(
                    'timestart' => 2000,
                ),
                'expectation'       => 1000,
            ),
            'Timed discussions disabled, timestart set but older' => array(
                'globalconfig'      => array(
                    'forum_enabletimedposts' => 0,
                ),
                'forumconfig'       => array(
                ),
                'postconfig'        => array(
                    'modified'  => 1000,
                ),
                'discussionconfig'  => array(
                    'timestart' => 500,
                ),
                'expectation'       => 1000,
            ),
            'Timed discussions enabled, timestart unset' => array(
                'globalconfig'      => array(
                    'forum_enabletimedposts' => 1,
                ),
                'forumconfig'       => array(
                ),
                'postconfig'        => array(
                    'modified'  => 1000,
                ),
                'discussionconfig'  => array(
                ),
                'expectation'       => 1000,
            ),
            'Timed discussions enabled, timestart set and newer' => array(
                'globalconfig'      => array(
                    'forum_enabletimedposts' => 1,
                ),
                'forumconfig'       => array(
                ),
                'postconfig'        => array(
                    'modified'  => 1000,
                ),
                'discussionconfig'  => array(
                    'timestart' => 2000,
                ),
                'expectation'       => 2000,
            ),
            'Timed discussions enabled, timestart set but older' => array(
                'globalconfig'      => array(
                    'forum_enabletimedposts' => 1,
                ),
                'forumconfig'       => array(
                ),
                'postconfig'        => array(
                    'modified'  => 1000,
                ),
                'discussionconfig'  => array(
                    'timestart' => 500,
                ),
                'expectation'       => 1000,
            ),
        );
    }

    
    public function test_postdate($globalconfig, $forumconfig, $postconfig, $discussionconfig, $expectation) {
        global $CFG, $DB;
        $this->resetAfterTest(true);

                foreach ($globalconfig as $key => $value) {
            $CFG->$key = $value;
        }

                $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', (object) array('course' => $course->id));
        $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

                $discussion = $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion(
            (object) array_merge($discussionconfig, array(
                'course'    => $course->id,
                'forum'     => $forum->id,
                'userid'    => $user->id,
            )));

                        $discussion = $DB->get_record('forum_discussions', array('id' => $discussion->id));
        foreach ($discussionconfig as $key => $value) {
            $discussion->$key = $value;
        }
        $DB->update_record('forum_discussions', $discussion);

                        $post = $DB->get_record('forum_posts', array('discussion' => $discussion->id));
        foreach ($postconfig as $key => $value) {
            $post->$key = $value;
        }
        $DB->update_record('forum_posts', $post);

                $renderable = new mod_forum\output\forum_post_email(
                $course,
                $cm,
                $forum,
                $discussion,
                $post,
                $user,
                $user,
                true
            );

                $this->assertEquals(userdate($expectation, "", \core_date::get_user_timezone($user)), $renderable->get_postdate());
    }
}
