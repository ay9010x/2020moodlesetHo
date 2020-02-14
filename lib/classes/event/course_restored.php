<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_restored extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcourserestored');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' restored the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'course_restored';
    }

    
    protected function get_legacy_eventdata() {
        return (object) array(
            'courseid' => $this->objectid,
            'userid' => $this->userid,
            'type' => $this->other['type'],
            'target' => $this->other['target'],
            'mode' => $this->other['mode'],
            'operation' => $this->other['operation'],
            'samesite' => $this->other['samesite'],
        );
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['type'])) {
            throw new \coding_exception('The \'type\' value must be set in other.');
        }

        if (!isset($this->other['target'])) {
            throw new \coding_exception('The \'target\' value must be set in other.');
        }

        if (!isset($this->other['mode'])) {
            throw new \coding_exception('The \'mode\' value must be set in other.');
        }

        if (!isset($this->other['operation'])) {
            throw new \coding_exception('The \'operation\' value must be set in other.');
        }

        if (!isset($this->other['samesite'])) {
            throw new \coding_exception('The \'samesite\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'course', 'restore' => 'course');
    }

    public static function get_other_mapping() {
                return false;
    }
}
