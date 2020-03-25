<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class submission_status_updated extends base {
    
    public static function create_from_submission(\assign $assign, \stdClass $submission) {
        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $submission->id,
            'relateduserid' => ($assign->get_instance()->teamsubmission) ? null : $submission->userid,
            'other' => array(
                'newstatus' => $submission->status
            )
        );
        
        $event = self::create($data);
        $event->set_assign($assign);
        $event->add_record_snapshot('assign_submission', $submission);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has updated the status of the submission with id '$this->objectid' for " .
            "the assignment with course module id '$this->contextinstanceid' to the status '{$this->other['newstatus']}'.";
    }

    
    public static function get_name() {
        return get_string('eventsubmissionstatusupdated', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'assign_submission';
    }

    
    protected function get_legacy_logdata() {
        $submission = $this->get_record_snapshot('assign_submission', $this->objectid);
        $user = $this->get_record_snapshot('user', $submission->userid);
        $logmessage = get_string('reverttodraftforstudent', 'assign', array('id' => $user->id, 'fullname' => fullname($user)));
        $this->set_legacy_logdata('revert submission to draft', $logmessage);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['newstatus'])) {
            throw new \coding_exception('The \'newstatus\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_submission', 'restore' => 'submission');
    }

    public static function get_other_mapping() {
                return false;
    }
}
