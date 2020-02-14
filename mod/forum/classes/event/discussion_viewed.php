<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class discussion_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'forum_discussions';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the discussion with id '$this->objectid' in the forum " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventdiscussionviewed', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/discuss.php', array('d' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'forum', 'view discussion', 'discuss.php?d=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'forum_discussions', 'restore' => 'forum_discussion');
    }
}

