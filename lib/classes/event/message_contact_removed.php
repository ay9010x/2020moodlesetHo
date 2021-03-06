<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class message_contact_removed extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'message_contacts';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventmessagecontactremoved', 'message');
    }

    
    public function get_url() {
        return new \moodle_url('/message/index.php', array('user1' => $this->userid, 'user2' => $this->relateduserid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' removed the user with id '$this->relateduserid' from their contact list.";
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'message', 'remove contact', 'index.php?user1=' . $this->relateduserid . '&amp;user2=' .
            $this->userid, $this->relateduserid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'message_contacts', 'restore' => base::NOT_MAPPED);
    }
}
