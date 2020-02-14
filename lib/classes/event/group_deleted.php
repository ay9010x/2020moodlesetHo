<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class group_deleted extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the group with id '$this->objectid'.";
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('groups', $this->objectid);
    }

    
    public static function get_legacy_eventname() {
        return 'groups_group_deleted';
    }

    
    public static function get_name() {
        return get_string('eventgroupdeleted', 'group');
    }

    
    public function get_url() {
        return new \moodle_url('/group/index.php', array('id' => $this->courseid));
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'groups';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'groups', 'restore' => 'group');
    }
}
