<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class statement_accepted extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_submission(\assign $assign, \stdClass $submission) {
        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $submission->id
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        $event->add_record_snapshot('assign_submission', $submission);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has accepted the statement of the submission with id '$this->objectid' " .
            "for the assignment with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventstatementaccepted', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'assign_submission';
    }

    
    protected function get_legacy_logdata() {
        global $USER;
        $logmessage = get_string('submissionstatementacceptedlog', 'mod_assign', fullname($USER));         $this->set_legacy_logdata('submission statement accepted', $logmessage);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call statement_accepted::create() directly, use statement_accepted::create_from_submission() instead.');
        }

        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_submission', 'restore' => 'submission');
    }
}
