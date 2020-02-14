<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class session_added extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendance_sessions';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' added a session to the instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventsessionadded', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/attendance/manage.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'report', 'report.php?id=' . $this->objectid,
            $this->other['info'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        if (empty($this->other['info'])) {
            throw new \coding_exception('The event mod_attendance\\event\\session_added must specify info.');
        }
        parent::validate_data();
    }
}
