<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class workflow_state_updated extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_user(\assign $assign, \stdClass $user, $state) {
        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $assign->get_instance()->id,
            'relateduserid' => $user->id,
            'other' => array(
                'newstate' => $state,
            ),
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        $event->add_record_snapshot('user', $user);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has set the workflow state of the user with id '$this->relateduserid' " .
            "to the state '{$this->other['newstate']}' for the assignment with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventworkflowstateupdated', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'assign';
    }

    
    protected function get_legacy_logdata() {
        $user = $this->get_record_snapshot('user', $this->relateduserid);
        $a = array('id' => $user->id, 'fullname' => fullname($user), 'state' => $this->other['newstate']);
        $logmessage = get_string('setmarkingworkflowstateforlog', 'assign', $a);
        $this->set_legacy_logdata('set marking workflow state', $logmessage);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call workflow_state_updated::create() directly, use workflow_state_updated::create_from_user() instead.');
        }

        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['newstate'])) {
            throw new \coding_exception('The \'newstate\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign', 'restore' => 'assign');
    }

    public static function get_other_mapping() {
                return false;
    }
}
