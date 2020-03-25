<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class course_reset_ended extends base {

    
    public function get_description() {
        return "The reset of the course with id '$this->courseid' has ended.";
    }

    
    public static function get_name() {
        return get_string('eventcourseresetended', 'core');
    }

    
    public function get_url() {
        return new \moodle_url('/course/view.php', array('id' => $this->courseid));
    }

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['reset_options'])) {
           throw new \coding_exception('The \'reset_options\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
