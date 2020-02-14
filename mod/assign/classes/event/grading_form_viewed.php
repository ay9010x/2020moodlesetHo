<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class grading_form_viewed extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_user(\assign $assign, \stdClass $user) {
        $data = array(
            'relateduserid' => $user->id,
            'context' => $assign->get_context(),
            'other' => array(
                'assignid' => $assign->get_instance()->id,
            ),
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        $event->add_record_snapshot('user', $user);
        return $event;
    }

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventgradingformviewed', 'mod_assign');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the grading form for the user with id '$this->relateduserid' " .
            "for the assignment with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        $user = $this->get_record_snapshot('user', $this->relateduserid);
        $msg = get_string('viewgradingformforstudent', 'assign',
            array('id' => $user->id, 'fullname' => fullname($user)));
        $this->set_legacy_logdata('view grading form', $msg);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call grading_form_viewed::create() directly, use grading_form_viewed::create_from_user() instead.');
        }

        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['assignid'])) {
            throw new \coding_exception('The \'assignid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['assignid'] = array('db' => 'assign', 'restore' => 'assign');

        return $othermapped;
    }
}
