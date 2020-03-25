<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class grouping_group_unassigned extends base {

    
    public function get_description() {
        return "The user with id '$this->userid' unassigned the group with id '{$this->other['groupid']}'" .
                " from the grouping with id '$this->objectid'.";
    }

    
    public static function get_name() {
        return get_string('eventgroupinggroupunassigned', 'group');
    }

    
    public function get_url() {
        return new \moodle_url('/group/assign.php', array('id' => $this->objectid));
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'groupings';
    }

    
    public static function get_objectid_mapping() {
        return array('db' => 'groupings', 'restore' => 'group');
    }

    
    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');
        return $othermapped;
    }
}
