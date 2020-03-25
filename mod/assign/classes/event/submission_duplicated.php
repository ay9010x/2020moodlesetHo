<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class submission_duplicated extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_submission(\assign $assign, \stdClass $submission) {
        $data = array(
            'objectid' => $submission->id,
            'context' => $assign->get_context(),
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        $event->add_record_snapshot('assign_submission', $submission);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' duplicated their submission with id '$this->objectid' for the " .
            "assignment with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventsubmissionduplicated', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'assign_submission';
    }

    
    protected function get_legacy_logdata() {
        $submission = $this->get_record_snapshot('assign_submission', $this->objectid);
        $this->set_legacy_logdata('submissioncopied', $this->assign->format_submission_for_log($submission));
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call submission_duplicated::create() directly, use submission_duplicated::create_from_submission() instead.');
        }

        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_submission', 'restore' => 'submission');
    }
}
