<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_completion_updated extends base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventcoursecompletionupdated', 'core_completion');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the requirements to complete the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/course/completion.php', array('id' => $this->courseid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'completion updated', 'completion.php?id=' . $this->courseid);
    }
}
