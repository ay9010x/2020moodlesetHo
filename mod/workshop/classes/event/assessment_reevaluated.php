<?php



namespace mod_workshop\event;
defined('MOODLE_INTERNAL') || die();


class assessment_reevaluated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'workshop_aggregations';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has had their assessment attempt reevaluated for the workshop with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'workshop', 'update aggregate grade', 'view.php?id=' . $this->contextinstanceid,
                $this->objectid, $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventassessmentreevaluated', 'mod_workshop');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/workshop/view.php', array('id' => $this->contextinstanceid));
    }

    public static function get_objectid_mapping() {
        return array('db' => 'workshop_aggregations', 'restore' => 'workshop_aggregation');
    }

    public static function get_other_mapping() {
                return false;
    }
}
