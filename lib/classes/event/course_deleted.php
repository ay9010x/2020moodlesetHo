<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_deleted extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcoursedeleted');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the course with id '$this->courseid'.";
    }

    
    public static function get_legacy_eventname() {
        return 'course_deleted';
    }

    
    protected function get_legacy_eventdata() {
        $course = $this->get_record_snapshot('course', $this->objectid);
        $course->context = $this->context;
        $course->timemodified = $this->data['timecreated'];
        return $course;
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'course', 'delete', 'view.php?id=' . $this->objectid, $this->other['fullname']  . '(ID ' . $this->objectid . ')');
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['fullname'])) {
            throw new \coding_exception('The \'fullname\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'course', 'restore' => 'course');
    }

    public static function get_other_mapping() {
                return false;
    }
}
