<?php


namespace report_outline\event;

defined('MOODLE_INTERNAL') || die();


class activity_report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventactivityreportviewed', 'report_outline');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the outline activity report for the course with id '$this->courseid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'report outline', "report/outline/index.php?id=$this->courseid", $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/outline/index.php', array('course' => $this->courseid));
    }

}
