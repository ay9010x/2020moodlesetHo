<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


class attempt_deleted extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the attempt with id '{$this->other['attemptid']}' " .
            "for the scorm activity with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventattemptdeleted', 'mod_scorm');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/scorm/report.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'scorm', 'delete attempts', 'report.php?id=' . $this->contextinstanceid,
                $this->other['attemptid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (empty($this->other['attemptid'])) {
            throw new \coding_exception('The \'attemptid\' must be set in other.');
        }
    }

    public static function get_other_mapping() {
                return false;
    }
}
