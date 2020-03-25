<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_login_failed extends base {
    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserloginfailed', 'auth');
    }

    
    public function get_description() {
                $username = s($this->other['username']);
        return "Login failed for the username '{$username}' for the reason with id '{$this->other['reason']}'.";
    }

    
    public function get_url() {
        if (isset($this->data['userid'])) {
            return new \moodle_url('/user/profile.php', array('id' => $this->data['userid']));
        } else {
            return null;
        }
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'login', 'error', 'index.php', $this->other['username']);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['reason'])) {
            throw new \coding_exception('The \'reason\' value must be set in other.');
        }

        if (!isset($this->other['username'])) {
            throw new \coding_exception('The \'username\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        return false;
    }
}
