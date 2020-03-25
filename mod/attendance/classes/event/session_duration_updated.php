<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class session_duration_updated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendance_sessions';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated attendance session duration with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventdurationupdated', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/attendance/manage.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'sessions duration updated', $this->get_url(),
            $this->other['info'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        if (empty($this->other['info'])) {
            throw new \coding_exception('The event mod_attendance\\event\\session_duration_updated must specify info.');
        }
        parent::validate_data();
    }
}
