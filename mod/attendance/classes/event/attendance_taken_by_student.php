<?php



namespace mod_attendance\event;
defined('MOODLE_INTERNAL') || die();


class attendance_taken_by_student extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'attendance_log';
    }

    
    public function get_description() {
        return 'Student with id ' . $this->userid . ' took attendance with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventtakenbystudent', 'mod_attendance');
    }

    
    public function get_url() {
        return new \moodle_url('/take.php', array('id' => $this->contextinstanceid,
                                                                 'sessionid' => $this->other['sessionid'],
                                                                 'grouptype' => $this->other['grouptype']));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'attendance', 'taken', $this->get_url(),
            '', $this->contextinstanceid);
    }

    
    protected function validate_data() {
        if (empty($this->other['sessionid'])) {
            throw new \coding_exception('The event mod_attendance\\event\\attendance_taken must specify sessionid.');
        }
        parent::validate_data();
    }
}
