<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class group_member_added extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' added the user with id '$this->relateduserid' to the group with " .
            "id '$this->objectid'.";
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->groupid = $this->objectid;
        $eventdata->userid  = $this->relateduserid;
        $eventdata->component = $this->other['component'];
        $eventdata->itemid = $this->other['itemid'];
        return $eventdata;
    }

    
    public static function get_legacy_eventname() {
        return 'groups_member_added';
    }

    
    public static function get_name() {
        return get_string('eventgroupmemberadded', 'group');
    }

    
    public function get_url() {
        return new \moodle_url('/group/members.php', array('group' => $this->objectid));
    }

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'groups';
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['component'])) {
            throw new \coding_exception('The \'component\' value must be set in other, even if empty.');
        }

        if (!isset($this->other['itemid'])) {
            throw new \coding_exception('The \'itemid\' value must be set in other, even if empty.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'groups', 'restore' => 'group');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['itemid'] = base::NOT_MAPPED;

        return $othermapped;
    }
}
