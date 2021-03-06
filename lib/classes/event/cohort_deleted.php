<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class cohort_deleted extends base {

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'cohort';
    }

    
    public static function get_name() {
        return get_string('eventcohortdeleted', 'core_cohort');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the cohort with id '$this->objectid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/cohort/index.php', array('contextid' => $this->contextid));
    }

    
    public static function get_legacy_eventname() {
        return 'cohort_deleted';
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('cohort', $this->objectid);
    }

    public static function get_objectid_mapping() {
                return array('db' => 'cohort', 'restore' => base::NOT_MAPPED);
    }
}
