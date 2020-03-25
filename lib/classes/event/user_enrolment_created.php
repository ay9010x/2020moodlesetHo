<?php


namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_enrolment_created extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'user_enrolments';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserenrolmentcreated', 'core_enrol');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' enrolled the user with id '$this->relateduserid' using the enrolment method " .
            "'{$this->other['enrol']}' in the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/enrol/users.php', array('id' => $this->courseid));
    }

    
    public static function get_legacy_eventname() {
        return 'user_enrolled';
    }

    
    protected function get_legacy_eventdata() {
        $legacyeventdata = $this->get_record_snapshot('user_enrolments', $this->objectid);
        $legacyeventdata->enrol = $this->other['enrol'];
        $legacyeventdata->courseid = $this->courseid;
        return $legacyeventdata;
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'enrol', '../enrol/users.php?id=' . $this->courseid, $this->courseid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['enrol'])) {
            throw new \coding_exception('The \'enrol\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'user_enrolments', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        return false;
    }
}
