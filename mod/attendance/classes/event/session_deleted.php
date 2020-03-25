<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class session_deleted extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendance_sessions';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' deleted session with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventsessiondeleted', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/attendance/manage.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'sessions deleted', $this->get_url(),
            $this->other['info'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        if (empty($this->other['info'])) {
            throw new \coding_exception('The event mod_attendance\\event\\session_deleted must specify info.');
        }
        parent::validate_data();
    }
}
