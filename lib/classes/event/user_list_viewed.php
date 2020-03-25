<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();



class user_list_viewed extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserlistviewed');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the list of users in the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/user/index.php', array('id' => $this->courseid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'user', 'view all', 'index.php?id=' . $this->courseid, '');
    }

    public static function get_objectid_mapping() {
        return array('db' => 'course', 'restore' => 'course');
    }

    public static function get_other_mapping() {
        return false;
    }
}
