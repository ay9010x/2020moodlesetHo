<?php


namespace mod_forum\event;
defined('MOODLE_INTERNAL') || die();


class discussion_pinned extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'forum_discussions';
    }

    
    public function get_description() {
        return "The user {$this->userid} has pinned the discussion {$this->objectid} in the forum {$this->other['forumid']}";
    }

    
    public static function get_name() {
        return get_string('eventdiscussionpinned', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/discuss.php', array('d' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
                $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/forum/'));
        return array($this->courseid, 'forum', 'pin discussion', $logurl, $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['forumid'])) {
            throw new \coding_exception('forumid must be set in $other.');
        }
        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context passed must be module context.');
        }
        if (!isset($this->objectid)) {
            throw new \coding_exception('objectid must be set to the discussionid.');
        }
    }

    
    public static function get_objectid_mapping() {
        return array('db' => 'forum_discussions', 'restore' => 'forum_discussion');
    }

    
    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['forumid'] = array('db' => 'forum', 'restore' => 'forum');

        return $othermapped;
    }
}
