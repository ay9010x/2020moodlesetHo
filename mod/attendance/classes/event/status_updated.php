<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class status_updated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendance_statuses';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated attendance status with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventstatusupdated', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/attendance/preferences.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'status updated', $this->get_url(),
            $this->other['updated'], $this->contextinstanceid);
    }
}
