<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class group_member_removed extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' removed the user with id '$this->relateduserid' to the group with " .
            "id '$this->objectid'.";
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->groupid = $this->objectid;
        $eventdata->userid  = $this->relateduserid;
        return $eventdata;
    }

    
    public static function get_legacy_eventname() {
        return 'groups_member_removed';
    }

    
    public static function get_name() {
        return get_string('eventgroupmemberremoved', 'group');
    }

    
    public function get_url() {
        return new \moodle_url('/group/members.php', array('group' => $this->objectid));
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'groups';
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'groups', 'restore' => 'group');
    }

}
