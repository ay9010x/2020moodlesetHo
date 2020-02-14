<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class course_completed extends base {
    
    public static function create_from_completion(\stdClass $completion) {
        $event = self::create(
            array(
                'objectid' => $completion->id,
                'relateduserid' => $completion->userid,
                'context' => \context_course::instance($completion->course),
                'courseid' => $completion->course,
                'other' => array('relateduserid' => $completion->userid),             )
        );
        $event->add_record_snapshot('course_completions', $completion);
        return $event;
    }

    
    protected function init() {
        $this->data['objecttable'] = 'course_completions';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventcoursecompleted', 'core_completion');
    }

    
    public function get_description() {
        return "The user with id '$this->relateduserid' completed the course with id '$this->courseid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/report/completion/index.php', array('course' => $this->courseid));
    }

    
    public static function get_legacy_eventname() {
        return 'course_completed';
    }

    
    protected function get_legacy_eventdata() {
        return $this->get_record_snapshot('course_completions', $this->objectid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

                        if (!isset($this->other['relateduserid'])) {
            throw new \coding_exception('The \'relateduserid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
                return array('db' => 'course_completions', 'restore' => base::NOT_MAPPED);
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['relateduserid'] = array('db' => 'user', 'restore' => 'user');
        return $othermapped;
    }
}
