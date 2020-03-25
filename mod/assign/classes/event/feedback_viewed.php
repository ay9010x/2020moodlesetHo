<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class feedback_viewed extends base {
    
    public static function create_from_grade(\assign $assign, \stdClass $grade) {
        $data = array(
            'objectid' => $grade->id,
            'relateduserid' => $grade->userid,
            'context' => $assign->get_context(),
            'other' => array(
                'assignid' => $assign->get_instance()->id,
            ),
        );
        
        $event = self::create($data);
        $event->set_assign($assign);
        $event->add_record_snapshot('assign_grades', $grade);
        return $event;
    }

    
    protected function init() {
        $this->data['objecttable'] = 'assign_grades';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventfeedbackviewed', 'mod_assign');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the feedback for the user with id '$this->relateduserid' " .
            "for the assignment with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        $logmessage = get_string('viewfeedbackforuser', 'assign', $this->relateduserid);
        $this->set_legacy_logdata('view feedback', $logmessage);
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['assignid'])) {
            throw new \coding_exception('The \'assignid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_grades', 'restore' => 'grade');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['assignid'] = array('db' => 'assign', 'restore' => 'assign');

        return $othermapped;
    }
}
