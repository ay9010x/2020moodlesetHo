<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_updated extends base {

    
    private $legacylogdata;

    
    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcourseupdated');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/course/edit.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'course_updated';
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('course', $this->objectid);
    }

    
    public function set_legacy_logdata($logdata) {
        $this->legacylogdata = $logdata;
    }

    
    protected function get_legacy_logdata() {
        return $this->legacylogdata;
    }

    public static function get_objectid_mapping() {
        return array('db' => 'course', 'restore' => 'course');
    }

    public static function get_other_mapping() {
                return false;
    }
}
