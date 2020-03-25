<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class note_deleted extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'post';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string("eventnotedeleted", "core_notes");
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the note with id '$this->objectid' for the user with id " .
            "'$this->relateduserid'";
    }

    
    protected function get_legacy_logdata() {
        $logurl = new \moodle_url('index.php', array('course' => $this->courseid, 'user' => $this->relateduserid));
        $logurl->set_anchor('note-' . $this->objectid);
        return array($this->courseid, 'notes', 'delete', $logurl, 'delete note');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'post', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
                return false;
    }
}
