<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class message_viewed extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'message_read';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventmessageviewed', 'message');
    }

    
    public function get_url() {
        return new \moodle_url('/message/index.php', array('user1' => $this->userid, 'user2' => $this->relateduserid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' read a message from the user with id '$this->relateduserid'.";
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['messageid'])) {
            throw new \coding_exception('The \'messageid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'message_read', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
                $othermapped = array();
                $othermapped['messageid'] = array('db' => base::NOT_MAPPED, 'restore' => base::NOT_MAPPED);
        return $othermapped;
    }
}
