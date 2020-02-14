<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_password_updated extends base {
    
    public static function create_from_user(\stdClass $user, $forgottenreset = false) {
        $data = array(
            'context' => \context_user::instance($user->id),
            'relateduserid' => $user->id,
            'other' => array('forgottenreset' => $forgottenreset),
        );
        $event = self::create($data);
        $event->add_record_snapshot('user', $user);
        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserpasswordupdated');
    }

    
    public function get_description() {
        if ($this->userid == $this->relateduserid) {
            if ($this->other['forgottenreset']) {
                return "The user with id '$this->userid' reset their password.";
            }
            return "The user with id '$this->userid' changed their password.";
        } else {
            return "The user with id '$this->userid' changed the password of the user with id '$this->relateduserid'.";
        }
    }

    
    public function get_url() {
        return new \moodle_url('/user/profile.php', array('id' => $this->relateduserid));
    }

    
    protected function get_legacy_logdata() {
        if (!$this->other['forgottenreset']) {
                        return null;
        }
        return array(SITEID, 'user', 'set password', 'profile.php?id='.$this->userid, $this->relateduserid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!$this->relateduserid) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['forgottenreset'])) {
            throw new \coding_exception('The \'forgottenreset\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}
