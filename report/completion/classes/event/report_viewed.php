<?php



namespace report_completion\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'report_completion');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the completion report for the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/report/completion/index.php', array('course' => $this->courseid));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }
    }
}
