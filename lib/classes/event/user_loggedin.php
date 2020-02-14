<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_loggedin extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' has logged in.";
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'user', 'login', 'view.php?id=' . $this->data['objectid'] . '&course=' . SITEID,
            $this->data['objectid'], 0, $this->data['objectid']);
    }

    
    public static function get_name() {
        return get_string('eventuserloggedin', 'auth');
    }

    
    public function get_url() {
        return new \moodle_url('/user/profile.php', array('id' => $this->data['objectid']));
    }

    
    public function get_username() {
        return $this->other['username'];
    }

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user';
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['username'])) {
            throw new \coding_exception('The \'username\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'user', 'restore' => 'user');
    }

    public static function get_other_mapping() {
        return false;
    }
}
