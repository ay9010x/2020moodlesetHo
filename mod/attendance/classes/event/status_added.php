<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class status_added extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendance_statuses';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated attendance status with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventstatusadded', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/attendance/preferences.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'status added', $this->get_url(),
            $this->other['acronym'].': '.$this->other['description'].' ('.$this->other['grade'].')', $this->contextinstanceid);
    }
}
