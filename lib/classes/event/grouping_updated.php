<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class grouping_updated extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' updated the grouping with id '$this->objectid'.";
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('groupings', $this->objectid);
    }

    
    public static function get_legacy_eventname() {
        return 'groups_grouping_updated';
    }

    
    public static function get_name() {
        return get_string('eventgroupingupdated', 'group');
    }

    
    public function get_url() {
        return new \moodle_url('/group/grouping.php', array('id' => $this->objectid));
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'groupings';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'groupings', 'restore' => 'grouping');
    }
}
