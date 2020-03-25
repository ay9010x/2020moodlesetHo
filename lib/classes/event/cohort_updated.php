<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class cohort_updated extends base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'cohort';
    }

    
    public static function get_name() {
        return get_string('eventcohortupdated', 'core_cohort');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the cohort with id '$this->objectid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/cohort/edit.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'cohort_updated';
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('cohort', $this->objectid);
    }

    public static function get_objectid_mapping() {
                return array('db' => 'cohort', 'restore' => base::NOT_MAPPED);
    }
}
