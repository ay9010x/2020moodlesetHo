<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class session_updated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'attendance_sessions';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated attendance session with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventsessionupdated', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/attendance/sessions.php', array('id' => $this->contextinstanceid,
                                                                     'sessionid' => $this->other['sessionid'],
                                                                     'action' => $this->other['action']));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'session updated', $this->get_url(),
            $this->other['info'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        if (empty($this->other['info'])) {
            throw new \coding_exception('The event mod_attendance\\event\\session_updated must specify info.');
        }
        if (empty($this->other['sessionid'])) {
            throw new \coding_exception('The event mod_attendance\\event\\session_updated must specify sessionid.');
        }
        if (empty($this->other['action'])) {
            throw new \coding_exception('The event mod_attendance\\event\\session_updated must specify action.');
        }
        parent::validate_data();
    }
}
