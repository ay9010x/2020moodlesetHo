<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_content_deleted extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcoursecontentdeleted');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted content from course with id '$this->courseid'.";
    }

    
    public static function get_legacy_eventname() {
        return 'course_content_removed';
    }

    
    protected function get_legacy_eventdata() {
        $course = $this->get_record_snapshot('course', $this->objectid);
        $course->context = $this->context;
        $course->options = $this->other['options'];

        return $course;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['options'])) {
            throw new \coding_exception('The \'options\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'course', 'restore' => 'course');
    }

    public static function get_other_mapping() {
        return false;
    }
}
