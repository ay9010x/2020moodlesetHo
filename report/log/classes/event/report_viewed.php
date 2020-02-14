<?php


namespace report_log\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'report_log');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the log report for the course with id '$this->courseid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, "course", "report log", "report/log/index.php?id=$this->courseid", $this->courseid);
    }

    
    public function get_url() {
        return new \moodle_url('/report/log/index.php', array('id' => $this->courseid));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['groupid'])) {
            throw new \coding_exception('The \'groupid\' value must be set in other.');
        }

        if (!isset($this->other['date'])) {
            throw new \coding_exception('The \'date\' value must be set in other.');
        }

        if (!isset($this->other['modid'])) {
            throw new \coding_exception('The \'modid\' value must be set in other.');
        }

        if (!isset($this->other['modaction'])) {
            throw new \coding_exception('The \'modaction\' value must be set in other.');
        }

        if (!isset($this->other['logformat'])) {
            throw new \coding_exception('The \'logformat\' value must be set in other.');
        }

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['modid'] = array('db' => 'course_modules', 'restore' => 'course_module');
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;
    }
}
