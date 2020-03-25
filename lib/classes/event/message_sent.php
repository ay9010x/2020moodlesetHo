<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class message_sent extends base {
    
    public static function create_from_ids($userfromid, $usertoid, $messageid) {
                                if (!\core_user::is_real_user($userfromid)) {
            $userfromid = 0;
        }

        $event = self::create(array(
            'userid' => $userfromid,
            'context' => \context_system::instance(),
            'relateduserid' => $usertoid,
            'other' => array(
                                                                'messageid' => $messageid
            )
        ));

        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventmessagesent', 'message');
    }

    
    public function get_url() {
        return new \moodle_url('/message/index.php', array('user1' => $this->userid, 'user2' => $this->relateduserid));
    }

    
    public function get_description() {
                if (\core_user::is_real_user($this->userid)) {
            return "The user with id '$this->userid' sent a message to the user with id '$this->relateduserid'.";
        }

        return "A message was sent by the system to the user with id '$this->relateduserid'.";
    }

    
    protected function get_legacy_logdata() {
                        if (\core_user::is_real_user($this->userid)) {
            return array(SITEID, 'message', 'write', 'index.php?user=' . $this->userid . '&id=' . $this->relateduserid .
                '&history=1#m' . $this->other['messageid'], $this->userid);
        }

        return null;
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
                return false;
    }

    public static function get_other_mapping() {
                $othermapped = array();
                $othermapped['messageid'] = array('db' => base::NOT_MAPPED, 'restore' => base::NOT_MAPPED);
        return $othermapped;
    }
}
