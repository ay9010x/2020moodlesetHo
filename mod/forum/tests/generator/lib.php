<?php



defined('MOODLE_INTERNAL') || die();



class mod_forum_generator extends testing_module_generator {

    
    protected $forumdiscussioncount = 0;

    
    protected $forumpostcount = 0;

    
    protected $forumsubscriptionscount = 0;

    
    public function reset() {
        $this->forumdiscussioncount = 0;
        $this->forumpostcount = 0;
        $this->forumsubscriptionscount = 0;

        parent::reset();
    }

    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/forum/lib.php');
        $record = (object)(array)$record;

        if (!isset($record->type)) {
            $record->type = 'general';
        }
        if (!isset($record->assessed)) {
            $record->assessed = 0;
        }
        if (!isset($record->scale)) {
            $record->scale = 0;
        }
        if (!isset($record->forcesubscribe)) {
            $record->forcesubscribe = FORUM_CHOOSESUBSCRIBE;
        }

        return parent::create_instance($record, (array)$options);
    }

    
    public function create_subscription($record = null) {
        global $DB;

                $this->forumsubscriptionscount++;

        $record = (array)$record;

        if (!isset($record['course'])) {
            throw new coding_exception('course must be present in phpunit_util::create_subscription() $record');
        }

        if (!isset($record['forum'])) {
            throw new coding_exception('forum must be present in phpunit_util::create_subscription() $record');
        }

        if (!isset($record['userid'])) {
            throw new coding_exception('userid must be present in phpunit_util::create_subscription() $record');
        }

        $record = (object)$record;

                $record->id = $DB->insert_record('forum_subscriptions', $record);

        return $record;
    }

    
    public function create_discussion($record = null) {
        global $DB;

                $this->forumdiscussioncount++;

        $record = (array) $record;

        if (!isset($record['course'])) {
            throw new coding_exception('course must be present in phpunit_util::create_discussion() $record');
        }

        if (!isset($record['forum'])) {
            throw new coding_exception('forum must be present in phpunit_util::create_discussion() $record');
        }

        if (!isset($record['userid'])) {
            throw new coding_exception('userid must be present in phpunit_util::create_discussion() $record');
        }

        if (!isset($record['name'])) {
            $record['name'] = "Discussion " . $this->forumdiscussioncount;
        }

        if (!isset($record['subject'])) {
            $record['subject'] = "Subject for discussion " . $this->forumdiscussioncount;
        }

        if (!isset($record['message'])) {
            $record['message'] = html_writer::tag('p', 'Message for discussion ' . $this->forumdiscussioncount);
        }

        if (!isset($record['messageformat'])) {
            $record['messageformat'] = editors_get_preferred_format();
        }

        if (!isset($record['messagetrust'])) {
            $record['messagetrust'] = "";
        }

        if (!isset($record['assessed'])) {
            $record['assessed'] = '1';
        }

        if (!isset($record['groupid'])) {
            $record['groupid'] = "-1";
        }

        if (!isset($record['timestart'])) {
            $record['timestart'] = "0";
        }

        if (!isset($record['timeend'])) {
            $record['timeend'] = "0";
        }

        if (!isset($record['mailnow'])) {
            $record['mailnow'] = "0";
        }

        if (isset($record['timemodified'])) {
            $timemodified = $record['timemodified'];
        }

        if (!isset($record['pinned'])) {
            $record['pinned'] = FORUM_DISCUSSION_UNPINNED;
        }

        if (isset($record['mailed'])) {
            $mailed = $record['mailed'];
        }

        $record = (object) $record;

                $record->id = forum_add_discussion($record, null, null, $record->userid);

        if (isset($timemodified) || isset($mailed)) {
            $post = $DB->get_record('forum_posts', array('discussion' => $record->id));

            if (isset($mailed)) {
                $post->mailed = $mailed;
            }

            if (isset($timemodified)) {
                                $record->timemodified = $timemodified;
                $post->modified = $post->created = $timemodified;

                $DB->update_record('forum_discussions', $record);
            }

            $DB->update_record('forum_posts', $post);
        }

        return $record;
    }

    
    public function create_post($record = null) {
        global $DB;

                $this->forumpostcount++;

                $time = time() + $this->forumpostcount;

        $record = (array) $record;

        if (!isset($record['discussion'])) {
            throw new coding_exception('discussion must be present in phpunit_util::create_post() $record');
        }

        if (!isset($record['userid'])) {
            throw new coding_exception('userid must be present in phpunit_util::create_post() $record');
        }

        if (!isset($record['parent'])) {
            $record['parent'] = 0;
        }

        if (!isset($record['subject'])) {
            $record['subject'] = 'Forum post subject ' . $this->forumpostcount;
        }

        if (!isset($record['message'])) {
            $record['message'] = html_writer::tag('p', 'Forum message post ' . $this->forumpostcount);
        }

        if (!isset($record['created'])) {
            $record['created'] = $time;
        }

        if (!isset($record['modified'])) {
            $record['modified'] = $time;
        }

        if (!isset($record['mailed'])) {
            $record['mailed'] = 0;
        }

        if (!isset($record['messageformat'])) {
            $record['messageformat'] = 0;
        }

        if (!isset($record['messagetrust'])) {
            $record['messagetrust'] = 0;
        }

        if (!isset($record['attachment'])) {
            $record['attachment'] = "";
        }

        if (!isset($record['totalscore'])) {
            $record['totalscore'] = 0;
        }

        if (!isset($record['mailnow'])) {
            $record['mailnow'] = 0;
        }

        $record = (object) $record;

                $record->id = $DB->insert_record('forum_posts', $record);

                forum_discussion_update_last_post($record->discussion);

        return $record;
    }

    public function create_content($instance, $record = array()) {
        global $USER, $DB;
        $record = (array)$record + array(
            'forum' => $instance->id,
            'userid' => $USER->id,
            'course' => $instance->course
        );
        if (empty($record['discussion']) && empty($record['parent'])) {
                        $discussion = $this->create_discussion($record);
            $post = $DB->get_record('forum_posts', array('id' => $discussion->firstpost));
        } else {
                        if (empty($record['parent'])) {
                $record['parent'] = $DB->get_field('forum_discussions', 'firstpost', array('id' => $record['discussion']), MUST_EXIST);
            } else if (empty($record['discussion'])) {
                $record['discussion'] = $DB->get_field('forum_posts', 'discussion', array('id' => $record['parent']), MUST_EXIST);
            }
            $post = $this->create_post($record);
        }
        return $post;
    }
}
