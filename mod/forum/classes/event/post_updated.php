<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class post_updated extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'forum_posts';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has updated the post with id '$this->objectid' in the discussion with " .
            "id '{$this->other['discussionid']}' in the forum with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventpostupdated', 'mod_forum');
    }

    
    public function get_url() {
        if ($this->other['forumtype'] == 'single' or $this->other['forumtype'] == 'snifs') {
                                                $url = new \moodle_url('/mod/forum/view.php', array('f' => $this->other['forumid']));
        } else {
            $url = new \moodle_url('/mod/forum/discuss.php', array('d' => $this->other['discussionid']));
        }
        $url->set_anchor('p'.$this->objectid);
        return $url;
    }

    
    protected function get_legacy_logdata() {
                $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/forum/'));

        return array($this->courseid, 'forum', 'update post', $logurl, $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['discussionid'])) {
            throw new \coding_exception('The \'discussionid\' value must be set in other.');
        }

        if (!isset($this->other['forumid'])) {
            throw new \coding_exception('The \'forumid\' value must be set in other.');
        }

        if (!isset($this->other['forumtype'])) {
            throw new \coding_exception('The \'forumtype\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'forum_posts', 'restore' => 'forum_post');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['forumid'] = array('db' => 'forum', 'restore' => 'forum');
        $othermapped['discussionid'] = array('db' => 'forum_discussions', 'restore' => 'forum_discussion');

        return $othermapped;
    }
}
