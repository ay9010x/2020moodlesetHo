<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class discussion_subscription_deleted extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'forum_discussion_subs';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' unsubscribed the user with id '$this->relateduserid' from the discussion " .
            " with id '{$this->other['discussion']}' in the forum with the course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventdiscussionsubscriptiondeleted', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/subscribe.php', array(
            'id' => $this->other['forumid'],
            'd' => $this->other['discussion'],
        ));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['forumid'])) {
            throw new \coding_exception('The \'forumid\' value must be set in other.');
        }

        if (!isset($this->other['discussion'])) {
            throw new \coding_exception('The \'discussion\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'forum_discussion_subs', 'restore' => 'forum_discussion_sub');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['forumid'] = array('db' => 'forum', 'restore' => 'forum');
        $othermapped['discussion'] = array('db' => 'forum_discussions', 'restore' => 'forum_discussion');

        return $othermapped;
    }
}
