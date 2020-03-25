<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();



class cohort_member_removed extends base {

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'cohort';
    }

    
    public static function get_name() {
        return get_string('eventcohortmemberremoved', 'core_cohort');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' removed the user with id '$this->relateduserid' from the cohort with " .
            "id '$this->objectid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/cohort/assign.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'cohort_member_removed';
    }

    
    protected function get_legacy_eventdata() {
        $data = new \stdClass();
        $data->cohortid = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'cohort', 'restore' => base::NOT_MAPPED);
    }
}
