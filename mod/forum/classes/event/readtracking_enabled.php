<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class readtracking_enabled extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has enabled read tracking for the user with id '$this->relateduserid' " .
            "in the forum with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventreadtrackingenabled', 'mod_forum');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/view.php', array('f' => $this->other['forumid']));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'forum', 'start tracking', 'view.php?f=' . $this->other['forumid'],
            $this->other['forumid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['forumid'])) {
            throw new \coding_exception('The \'forumid\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['forumid'] = array('db' => 'forum', 'restore' => 'forum');

        return $othermapped;
    }
}
