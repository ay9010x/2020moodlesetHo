<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class marker_updated extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_marker(\assign $assign, \stdClass $user, \stdClass $marker) {
        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $assign->get_instance()->id,
            'relateduserid' => $user->id,
            'other' => array(
                'markerid' => $marker->id,
            ),
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        $event->add_record_snapshot('user', $user);
        $event->add_record_snapshot('user', $marker);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has set the marker for the user with id '$this->relateduserid' to " .
            "'{$this->other['markerid']}' for the assignment with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventmarkerupdated', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'assign';
    }

    
    protected function get_legacy_logdata() {
        $user = $this->get_record_snapshot('user', $this->relateduserid);
        $marker = $this->get_record_snapshot('user', $this->other['markerid']);
        $a = array('id' => $user->id, 'fullname' => fullname($user), 'marker' => fullname($marker));
        $logmessage = get_string('setmarkerallocationforlog', 'assign', $a);
        $this->set_legacy_logdata('set marking allocation', $logmessage);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call marker_updated::create() directly, use marker_updated::create_from_marker() instead.');
        }

        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['markerid'])) {
            throw new \coding_exception('The \'markerid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign', 'restore' => 'assign');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['markerid'] = array('db' => 'user', 'restore' => 'user');

        return $othermapped;
    }
}
