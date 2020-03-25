<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class submission_form_viewed extends base {
    
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
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventsubmissionformviewed', 'mod_assign');
    }

    
    public function get_description() {
        if ($this->userid != $this->relateduserid) {
            return "The user with id '$this->userid' viewed the submission form for the user with id '$this->relateduserid' " .
                "for the assignment with course module id '$this->contextinstanceid'.";
        }

        return "The user with id '$this->userid' viewed their submission for the assignment with course module id " .
            "'$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        if ($this->relateduserid == $this->userid) {
            $title = get_string('editsubmission', 'assign');
        } else {
            $user = $this->get_record_snapshot('user', $this->relateduserid);
            $title = get_string('editsubmissionother', 'assign', fullname($user));
        }
        $this->set_legacy_logdata('view submit assignment form', $title);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call submission_form_viewed::create() directly, use submission_form_viewed::create_from_user() instead.');
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
