<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class group_updated extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' updated the group with id '$this->objectid'.";
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('groups', $this->objectid);
    }

    
    public static function get_legacy_eventname() {
        return 'groups_group_updated';
    }

    
    public static function get_name() {
        return get_string('eventgroupupdated', 'group');
    }

    
    public function get_url() {
        return new \moodle_url('/group/group.php', array('id' => $this->objectid));
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'groups';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'groups', 'restore' => 'group');
    }
}
