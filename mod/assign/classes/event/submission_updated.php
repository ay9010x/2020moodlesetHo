<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


abstract class submission_updated extends base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventsubmissionupdated', 'mod_assign');
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['submissionid'])) {
            throw new \coding_exception('The \'submissionid\' value must be set in other.');
        }
        if (!isset($this->other['submissionattempt'])) {
            throw new \coding_exception('The \'submissionattempt\' value must be set in other.');
        }
        if (!isset($this->other['submissionstatus'])) {
            throw new \coding_exception('The \'submissionstatus\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['submissionid'] = array('db' => 'assign_submission', 'restore' => 'submission');
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;
    }
}
