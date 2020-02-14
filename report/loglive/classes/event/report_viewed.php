<?php


namespace report_loglive\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'report_loglive');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the live log report for the course with id '$this->courseid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course', 'report live', "report/loglive/index.php?id=$this->courseid", $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/loglive/index.php', array('id' => $this->courseid));
    }
}
