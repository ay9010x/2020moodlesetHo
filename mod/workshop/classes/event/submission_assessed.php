<?php



namespace mod_workshop\event;
defined('MOODLE_INTERNAL') || die();


class submission_assessed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'workshop_assessments';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' assessed the submission with id '$this->objectid' for the user with " .
            "id '$this->relateduserid' in the workshop with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'workshop', 'add assessment ', 'assessment.php?asid=' . $this->objectid,
            $this->other['submissionid'], $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventsubmissionassessed', 'mod_workshop');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/workshop/assessment.php', array('asid' => $this->objectid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['submissionid'])) {
            throw new \coding_exception('The \'submissionid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'workshop_assessments', 'restore' => 'workshop_assessment');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['submissionid'] = array('db' => 'workshop_submissions', 'restore' => 'workshop_submission');
        $othermapped['workshopid'] = array('db' => 'workshop', 'restore' => 'workshop');

        return $othermapped;
    }
}
