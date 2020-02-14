<?php


namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_loggedout extends base {

    
    protected function init() {
        $this->context = \context_system::instance();
        $this->data['objecttable'] = 'user';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserloggedout');
    }

    
    public function get_description() {
        return "The user with id '$this->objectid' has logged out.";
    }

    
    public function get_url() {
        return new \moodle_url('/user/view.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'user_logout';
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('user', $this->objectid);
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'user', 'logout', 'view.php?id='.$this->objectid.'&course='.SITEID, $this->objectid, 0,
            $this->objectid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'user', 'restore' => 'user');
    }

    public static function get_other_mapping() {
        return false;
    }
}
