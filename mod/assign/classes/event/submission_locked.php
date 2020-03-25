<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class submission_locked extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_user(\assign $assign, \stdClass $user) {
        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $assign->get_instance()->id,
            'relateduserid' => $user->id,
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        $event->add_record_snapshot('user', $user);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' locked the submission for the user with id '$this->relateduserid' for " .
            "the assignment with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventsubmissionlocked', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'assign';
    }

    
    protected function get_legacy_logdata() {
        $user = $this->get_record_snapshot('user', $this->relateduserid);
        $logmessage = get_string('locksubmissionforstudent', 'assign', array('id' => $user->id, 'fullname' => fullname($user)));
        $this->set_legacy_logdata('lock submission', $logmessage);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call submission_locked::create() directly, use submission_locked::create_from_user() instead.');
        }

        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign', 'restore' => 'assign');
    }
}
