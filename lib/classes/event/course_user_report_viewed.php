<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class course_user_report_viewed extends base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the user report for the course with id '$this->courseid' " .
            "for user with id '$this->relateduserid'.";
    }

    
    public static function get_name() {
        return get_string('eventcourseuserreportviewed', 'core');
    }

    
    public function get_url() {
        return new \moodle_url("/course/user.php", array('id' => $this->courseid, 'user' => $this->relateduserid,
                'mode' => $this->other['mode']));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'user report', 'user.php?id=' . $this->courseid . '&amp;user='
                . $this->relateduserid . '&amp;mode=' . $this->other['mode'], $this->relateduserid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if ($this->contextlevel != CONTEXT_COURSE) {
            throw new \coding_exception('Context passed must be course context.');
        }

        if (empty($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

                if (!isset($this->other['mode'])) {
            throw new \coding_exception('The \'mode\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
