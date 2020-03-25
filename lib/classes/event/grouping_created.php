<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class grouping_created extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' created the grouping with id '$this->objectid'.";
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('groupings', $this->objectid);
    }

    
    public static function get_legacy_eventname() {
        return 'groups_grouping_created';
    }

    
    public static function get_name() {
        return get_string('eventgroupingcreated', 'group');
    }

    
    public function get_url() {
        return new \moodle_url('/group/groupings.php', array('id' => $this->courseid));
    }

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'groupings';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'groupings', 'restore' => 'grouping');
    }
}
