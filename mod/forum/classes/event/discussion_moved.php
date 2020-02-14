<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class discussion_moved extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'forum_discussions';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has moved the discussion with id '$this->objectid' from the " .
            "forum with id '{$this->other['fromforumid']}' to the forum with id '{$this->other['toforumid']}'.";
    }

    
    public static function get_name() {
        return get_string('eventdiscussionmoved', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/discuss.php', array('d' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'forum', 'move discussion', 'discuss.php?d=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['fromforumid'])) {
            throw new \coding_exception('The \'fromforumid\' value must be set in other.');
        }

        if (!isset($this->other['toforumid'])) {
            throw new \coding_exception('The \'toforumid\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'forum_discussions', 'restore' => 'forum_discussion');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['fromforumid'] = array('db' => 'forum', 'restore' => 'forum');
        $othermapped['toforumid'] = array('db' => 'forum', 'restore' => 'forum');

        return $othermapped;
    }
}
