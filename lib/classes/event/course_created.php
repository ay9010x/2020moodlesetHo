<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_created extends base {

    
    protected function init() {
        $this->data['objecttable'] = 'course';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventcoursecreated');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->objectid));
    }

    
    public static function get_legacy_eventname() {
        return 'course_created';
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('course', $this->objectid);
    }

    
    protected function get_legacy_logdata() {
        return array(SITEID, 'course', 'new', 'view.php?id=' . $this->objectid, $this->other['fullname'] . ' (ID ' . $this->objectid . ')');
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
