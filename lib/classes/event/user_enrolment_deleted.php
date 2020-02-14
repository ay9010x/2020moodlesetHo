<?php


namespace core\event;

defined('MOODLE_INTERNAL') || die();


class user_enrolment_deleted extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'user_enrolments';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventuserenrolmentdeleted', 'core_enrol');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' unenrolled the user with id '$this->relateduserid' using the enrolment method " .
            "'{$this->other['enrol']}' from the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/enrol/users.php', array('id' => $this->courseid));
    }

    
    public static function get_legacy_eventname() {
        return 'user_unenrolled';
    }

    
    protected function get_legacy_eventdata() {
        return (object)$this->other['userenrolment'];
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'unenrol', '../enrol/users.php?id=' . $this->courseid, $this->courseid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['userenrolment'])) {
            throw new \coding_exception('The \'userenrolment\' value must be set in other.');
        }
        if (!isset($this->other['enrol'])) {
            throw new \coding_exception('The \'enrol\' value must be set in other.');
        }
        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'user_enrolments', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        return false;
    }
}
