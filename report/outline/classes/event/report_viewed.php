<?php


namespace report_outline\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventoutlinereportviewed', 'report_outline');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the outline report for the user with id '$this->relateduserid' " .
            "for the course with id '$this->courseid'.";
    }

    
    protected function get_legacy_logdata() {
        $url = "report/outline/user.php?id=". $this->userid . "&course=" . $this->courseid . "&mode=" . $this->other['mode'];
        return array($this->courseid, 'course', 'report outline', $url, $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/outline/user.php', array('course' => $this->courseid, 'id' => $this->relateduserid,
                'mode' => $this->other['mode']));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->other['mode'])) {
            throw new \coding_exception('The \'mode\' value must be set in other.');
        }
        if (empty($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }

}
