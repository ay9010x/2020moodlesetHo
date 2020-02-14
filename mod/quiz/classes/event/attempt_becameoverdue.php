<?php


namespace mod_quiz\event;
defined('MOODLE_INTERNAL') || die();


class attempt_becameoverdue extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'quiz_attempts';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        return "The quiz attempt with id '$this->objectid' belonging to the quiz with course module id '$this->contextinstanceid' " .
            "for the user with id '$this->relateduserid' became overdue.";
    }

    
    public static function get_name() {
        return get_string('eventquizattempttimelimitexceeded', 'mod_quiz');
    }

    
    static public function get_legacy_eventname() {
        return 'quiz_attempt_overdue';
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/review.php', array('attempt' => $this->objectid));
    }

    
    protected function get_legacy_eventdata() {
        $attempt = $this->get_record_snapshot('quiz_attempts', $this->objectid);

        $legacyeventdata = new \stdClass();
        $legacyeventdata->component = 'mod_quiz';
        $legacyeventdata->attemptid = $this->objectid;
        $legacyeventdata->timestamp = $attempt->timemodified;
        $legacyeventdata->userid = $this->relateduserid;
        $legacyeventdata->quizid = $attempt->quiz;
        $legacyeventdata->cmid = $this->contextinstanceid;
        $legacyeventdata->courseid = $this->courseid;
        $legacyeventdata->submitterid = $this->other['submitterid'];

        return $legacyeventdata;
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!array_key_exists('submitterid', $this->other)) {
            throw new \coding_exception('The \'submitterid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'quiz_attempts', 'restore' => 'quiz_attempt');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['submitterid'] = array('db' => 'user', 'restore' => 'user');
        $othermapped['quizid'] = array('db' => 'quiz', 'restore' => 'quiz');

        return $othermapped;
    }
}
