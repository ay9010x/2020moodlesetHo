<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class assessable_submitted extends base {
    
    public static function create_from_submission(\assign $assign, \stdClass $submission, $editable) {
        global $USER;

        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $submission->id,
            'other' => array(
                'submission_editable' => $editable,
            ),
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $data['relateduserid'] = $submission->userid;
        }
        
        $event = self::create($data);
        $event->set_assign($assign);
        $event->add_record_snapshot('assign_submission', $submission);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has submitted the submission with id '$this->objectid' " .
            "for the assignment with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->contextinstanceid;
        $eventdata->itemid = $this->objectid;
        $eventdata->courseid = $this->courseid;
        $eventdata->userid = $this->userid;
        $eventdata->params = array('submission_editable' => $this->other['submission_editable']);
        return $eventdata;
    }

    
    public static function get_legacy_eventname() {
        return 'assessable_submitted';
    }

    
    public static function get_name() {
        return get_string('eventassessablesubmitted', 'mod_assign');
    }

    
    protected function init() {
        $this->data['objecttable'] = 'assign_submission';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    protected function get_legacy_logdata() {
        $submission = $this->get_record_snapshot('assign_submission', $this->objectid);
        $this->set_legacy_logdata('submit for grading', $this->assign->format_submission_for_log($submission));
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['submission_editable'])) {
            throw new \coding_exception('The \'submission_editable\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_submission', 'restore' => 'submission');
    }

    public static function get_other_mapping() {
                return false;
    }
}
