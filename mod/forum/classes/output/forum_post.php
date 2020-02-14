<?php



namespace mod_forum\output;

defined('MOODLE_INTERNAL') || die();


class forum_post implements \renderable, \templatable {

    
    protected $course = null;

    
    protected $cm = null;

    
    protected $forum = null;

    
    protected $discussion = null;

    
    protected $post = null;

    
    protected $canreply = false;

    
    protected $viewfullnames = false;

    
    protected $userto = null;

    
    protected $author = null;

    
    protected $writablekeys = array(
        'viewfullnames'    => true,
    );

    
    public function __construct($course, $cm, $forum, $discussion, $post, $author, $recipient, $canreply) {
        $this->course = $course;
        $this->cm = $cm;
        $this->forum = $forum;
        $this->discussion = $discussion;
        $this->post = $post;
        $this->author = $author;
        $this->userto = $recipient;
        $this->canreply = $canreply;
    }

    
    public function export_for_template(\renderer_base $renderer, $plaintext = false) {
        if ($plaintext) {
            return $this->export_for_template_text($renderer);
        } else {
            return $this->export_for_template_html($renderer);
        }
    }

    
    protected function export_for_template_text(\mod_forum_renderer $renderer) {
        return array(
            'id'                            => html_entity_decode($this->post->id),
            'coursename'                    => html_entity_decode($this->get_coursename()),
            'courselink'                    => html_entity_decode($this->get_courselink()),
            'forumname'                     => html_entity_decode($this->get_forumname()),
            'showdiscussionname'            => html_entity_decode($this->get_showdiscussionname()),
            'discussionname'                => html_entity_decode($this->get_discussionname()),
            'subject'                       => html_entity_decode($this->get_subject()),
            'authorfullname'                => html_entity_decode($this->get_author_fullname()),
            'postdate'                      => html_entity_decode($this->get_postdate()),

                        'message'                       => html_entity_decode($renderer->format_message_text($this->cm, $this->post)),
            'attachments'                   => html_entity_decode($renderer->format_message_attachments($this->cm, $this->post)),

            'canreply'                      => $this->canreply,
            'permalink'                     => $this->get_permalink(),
            'firstpost'                     => $this->get_is_firstpost(),
            'replylink'                     => $this->get_replylink(),
            'unsubscribediscussionlink'     => $this->get_unsubscribediscussionlink(),
            'unsubscribeforumlink'          => $this->get_unsubscribeforumlink(),
            'parentpostlink'                => $this->get_parentpostlink(),

            'forumindexlink'                => $this->get_forumindexlink(),
            'forumviewlink'                 => $this->get_forumviewlink(),
            'discussionlink'                => $this->get_discussionlink(),

            'authorlink'                    => $this->get_authorlink(),
            'authorpicture'                 => $this->get_author_picture(),

            'grouppicture'                  => $this->get_group_picture(),
        );
    }

    
    protected function export_for_template_html(\mod_forum_renderer $renderer) {
        return array(
            'id'                            => $this->post->id,
            'coursename'                    => $this->get_coursename(),
            'courselink'                    => $this->get_courselink(),
            'forumname'                     => $this->get_forumname(),
            'showdiscussionname'            => $this->get_showdiscussionname(),
            'discussionname'                => $this->get_discussionname(),
            'subject'                       => $this->get_subject(),
            'authorfullname'                => $this->get_author_fullname(),
            'postdate'                      => $this->get_postdate(),

                        'message'                       => $renderer->format_message_text($this->cm, $this->post),
            'attachments'                   => $renderer->format_message_attachments($this->cm, $this->post),

            'canreply'                      => $this->canreply,
            'permalink'                     => $this->get_permalink(),
            'firstpost'                     => $this->get_is_firstpost(),
            'replylink'                     => $this->get_replylink(),
            'unsubscribediscussionlink'     => $this->get_unsubscribediscussionlink(),
            'unsubscribeforumlink'          => $this->get_unsubscribeforumlink(),
            'parentpostlink'                => $this->get_parentpostlink(),

            'forumindexlink'                => $this->get_forumindexlink(),
            'forumviewlink'                 => $this->get_forumviewlink(),
            'discussionlink'                => $this->get_discussionlink(),

            'authorlink'                    => $this->get_authorlink(),
            'authorpicture'                 => $this->get_author_picture(),

            'grouppicture'                  => $this->get_group_picture(),
        );
    }

    
    public function __set($key, $value) {
                $methodname = 'set_' . $key;
        if (method_exists($this, $methodname)) {
            return $this->{$methodname}($value);
        }

                if (isset($this->writablekeys[$key]) && $this->writablekeys[$key]) {
            return $this->{$key} = $value;
        }

                throw new \coding_exception('Tried to set unknown property "' . $key . '"');
    }

    
    public function get_is_firstpost() {
        return empty($this->post->parent);
    }

    
    public function get_courselink() {
        $link = new \moodle_url(
                        '/course/view.php', array(
                'id'    => $this->course->id,
            )
        );

        return $link->out(false);
    }

    
    public function get_forumindexlink() {
        $link = new \moodle_url(
                        '/mod/forum/index.php', array(
                'id'    => $this->course->id,
            )
        );

        return $link->out(false);
    }

    
    public function get_forumviewlink() {
        $link = new \moodle_url(
                        '/mod/forum/view.php', array(
                'f' => $this->forum->id,
            )
        );

        return $link->out(false);
    }

    
    protected function _get_discussionlink() {
        return new \moodle_url(
                        '/mod/forum/discuss.php', array(
                                'd' => $this->discussion->id,
            )
        );
    }

    
    public function get_discussionlink() {
        $link = $this->_get_discussionlink();

        return $link->out(false);
    }

    
    public function get_permalink() {
        $link = $this->_get_discussionlink();
        $link->set_anchor($this->get_postanchor());

        return $link->out(false);
    }

    
    public function get_parentpostlink() {
        $link = $this->_get_discussionlink();
        $link->param('parent', $this->post->parent);

        return $link->out(false);
    }

    
    public function get_authorlink() {
        $link = new \moodle_url(
            '/user/view.php', array(
                'id' => $this->post->userid,
                'course' => $this->course->id,
            )
        );

        return $link->out(false);
    }

    
    public function get_unsubscribeforumlink() {
        if (!\mod_forum\subscriptions::is_subscribable($this->forum)) {
            return null;
        }
        $link = new \moodle_url(
            '/mod/forum/subscribe.php', array(
                'id' => $this->forum->id,
            )
        );

        return $link->out(false);
    }

    
    public function get_unsubscribediscussionlink() {
        if (!\mod_forum\subscriptions::is_subscribable($this->forum)) {
            return null;
        }
        $link = new \moodle_url(
            '/mod/forum/subscribe.php', array(
                'id'  => $this->forum->id,
                'd'   => $this->discussion->id,
            )
        );

        return $link->out(false);
    }

    
    public function get_replylink() {
        return new \moodle_url(
            '/mod/forum/post.php', array(
                'reply' => $this->post->id,
            )
        );
    }

    
    public function get_subject() {
        return format_string($this->post->subject, true);
    }

    
    public function get_postanchor() {
        return 'p' . $this->post->id;
    }

    
    public function get_courseidnumber() {
        return s($this->course->idnumber);
    }

    
    public function get_coursefullname() {
        return format_string($this->course->fullname, true, array(
            'context' => \context_course::instance($this->course->id),
        ));
    }

    
    public function get_coursename() {
        return format_string($this->course->shortname, true, array(
            'context' => \context_course::instance($this->course->id),
        ));
    }

    
    public function get_forumname() {
        return format_string($this->forum->name, true);
    }

    
    public function get_discussionname() {
        return format_string($this->discussion->name, true);
    }

    
    public function get_showdiscussionname() {
        return ($this->forum->name !== $this->discussion->name);
    }

    
    public function get_author_fullname() {
        return fullname($this->author, $this->viewfullnames);
    }

    
    protected function get_postto() {
        global $USER;
        if (null === $this->userto) {
            return $USER;
        }

        return $this->userto;
    }

    
    public function get_postdate() {
        global $CFG;

        $postmodified = $this->post->modified;
        if (!empty($CFG->forum_enabletimedposts) && ($this->discussion->timestart > $postmodified)) {
            $postmodified = $this->discussion->timestart;
        }

        return userdate($postmodified, "", \core_date::get_user_timezone($this->get_postto()));
    }

    
    public function get_author_picture() {
        global $OUTPUT;

        return $OUTPUT->user_picture($this->author, array('courseid' => $this->course->id));
    }

    
    public function get_group_picture() {
        if (isset($this->userfrom->groups)) {
            $groups = $this->userfrom->groups[$this->forum->id];
        } else {
            $groups = groups_get_all_groups($this->course->id, $this->author->id, $this->cm->groupingid);
        }

        if ($this->get_is_firstpost()) {
            return print_group_picture($groups, $this->course->id, false, true, true);
        }
    }
}
