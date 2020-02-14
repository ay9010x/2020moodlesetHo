<?php



namespace mod_chat\event;
defined('MOODLE_INTERNAL') || die();


class message_sent extends \core\event\base {

    
    public function get_description() {
        return "The user with id '$this->relateduserid' has sent a message in the chat with course module id
            '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        $message = $this->get_record_snapshot('chat_messages', $this->objectid);
        return array($this->courseid, 'chat', 'talk', 'view.php?id=' . $this->contextinstanceid,
            $message->chatid, $this->contextinstanceid, $this->relateduserid);
    }

    
    public static function get_name() {
        return get_string('eventmessagesent', 'mod_chat');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/chat/view.php', array('id' => $this->contextinstanceid));
    }

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'chat_messages';
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'chat_messages', 'restore' => 'chat_message');
    }
}
