<?php



namespace mod_assign\event;

defined('MOODLE_INTERNAL') || die();


class submission_graded extends base {
    
    protected static $preventcreatecall = true;

    
    public static function create_from_grade(\assign $assign, \stdClass $grade) {
        $data = array(
            'context' => $assign->get_context(),
            'objectid' => $grade->id,
            'relateduserid' => $grade->userid
        );
        self::$preventcreatecall = false;
        
        $event = self::create($data);
        self::$preventcreatecall = true;
        $event->set_assign($assign);
        $event->add_record_snapshot('assign_grades', $grade);
        return $event;
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has graded the submission '$this->objectid' for the user with " .
            "id '$this->relateduserid' for the assignment with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventsubmissiongraded', 'mod_assign');
    }

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'assign_grades';
    }

    
    protected function get_legacy_logdata() {
        $grade = $this->get_record_snapshot('assign_grades', $this->objectid);
        $this->set_legacy_logdata('grade submission', $this->assign->format_grade_for_log($grade));
        return parent::get_legacy_logdata();
    }

    
    protected function validate_data() {
        if (self::$preventcreatecall) {
            throw new \coding_exception('cannot call submission_graded::create() directly, use submission_graded::create_from_grade() instead.');
        }

        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'assign_grades', 'restore' => 'grade');
    }
}
