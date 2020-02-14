<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendance';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' viewed attendance report with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/attendance/report.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'report', 'report.php?id=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }
}
