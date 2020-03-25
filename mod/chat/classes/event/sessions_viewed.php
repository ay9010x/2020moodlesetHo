<?php



namespace mod_chat\event;
defined('MOODLE_INTERNAL') || die();


class sessions_viewed extends \core\event\base {

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the sessions of the chat with course module id
            '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'chat', 'report', 'report.php?id=' . $this->contextinstanceid,
            $this->objectid, $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventsessionsviewed', 'mod_chat');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/chat/report.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'chat';
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['start'])) {
            throw new \coding_exception('The \'start\' value must be set in other.');
        }
        if (!isset($this->other['end'])) {
            throw new \coding_exception('The \'end\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'chat', 'restore' => 'chat');
    }

    public static function get_other_mapping() {
                return false;
    }
}
