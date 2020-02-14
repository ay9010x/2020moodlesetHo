<?php



namespace mod_workshop\event;
defined('MOODLE_INTERNAL') || die();


class submission_created extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'workshop_submissions';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the submission with id of '$this->objectid' for the workshop " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventsubmissioncreated', 'workshop');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/workshop/submission.php',
                array('cmid' => $this->contextinstanceid, 'id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'workshop', 'add submission',
            'submission.php?cmid=' . $this->contextinstanceid . '&id=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'workshop_submissions', 'restore' => 'workshop_submission');
    }

    public static function get_other_mapping() {
                return false;
    }
}
