<?php



namespace mod_workshop\event;
defined('MOODLE_INTERNAL') || die();


class assessment_evaluated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'workshop_aggregations';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has had their assessment attempt evaluated for the workshop with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventassessmentevaluated', 'mod_workshop');
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
