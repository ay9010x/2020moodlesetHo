<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class recent_activity_viewed extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventrecentactivityviewed', 'core');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the recent activity report in the course with id '$this->courseid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, "course", "recent", "recent.php?id=$this->courseid", $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/course/recent.php', array('id' => $this->courseid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context level must be CONTEXT_COURSE.');
        }
    }
}

