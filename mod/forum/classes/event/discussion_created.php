<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class discussion_created extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'forum_discussions';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has created the discussion with id '$this->objectid' in the forum " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventdiscussioncreated', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/discuss.php', array('d' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {

                $logurl = substr($this->get_url()->out_as_local_url(), strlen('/mod/forum/'));

        return array($this->courseid, 'forum', 'add discussion', $logurl, $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['forumid'])) {
            throw new \coding_exception('The \'forumid\' value must be set in other.');
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
        $othermapped['forumid'] = array('db' => 'forum', 'restore' => 'forum');

        return $othermapped;
    }
}
