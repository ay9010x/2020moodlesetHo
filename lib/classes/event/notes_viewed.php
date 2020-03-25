<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class notes_viewed extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string("eventnotesviewed", "core_notes");
    }

    
    public function get_description() {
        if (!empty($this->relateduserid)) {
            return "The user with id '$this->userid' viewed the notes for the user with id '$this->relateduserid'.";
        }

        return "The user with id '$this->userid' viewed the notes for the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/notes/index.php', array('course' => $this->courseid, 'user' => $this->relateduserid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'notes', 'view', 'index.php?course=' . $this->courseid.'&amp;user=' . $this->relateduserid,
            'view notes');
    }
}
