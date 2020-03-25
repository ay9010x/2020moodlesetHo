<?php


namespace mod_quiz\event;
defined('MOODLE_INTERNAL') || die();


class attempt_started extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'quiz_attempts';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public function get_description() {
        return "The user with id '$this->relateduserid' has started the attempt with id '$this->objectid' for the " .
            "quiz with course module id '$this->contextinstanceid'.";
    }

    
    public static function get_name() {
        return get_string('eventquizattemptstarted', 'mod_quiz');
    }

    
    static public function get_legacy_eventname() {
        return 'quiz_attempt_started';
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/review.php', array('attempt' => $this->objectid));
    }

    
    protected function get_legacy_eventdata() {
        $attempt = $this->get_record_snapshot('quiz_attempts', $this->objectid);

        $legacyeventdata = new \stdClass();
        $legacyeventdata->component = 'mod_quiz';
        $legacyeventdata->attemptid = $attempt->id;
        $legacyeventdata->timestart = $attempt->timestart;
        $legacyeventdata->timestamp = $attempt->timestart;
        $legacyeventdata->userid = $this->relateduserid;
        $legacyeventdata->quizid = $attempt->quiz;
        $legacyeventdata->cmid = $this->contextinstanceid;
        $legacyeventdata->courseid = $this->courseid;

        return $legacyeventdata;
    }

    
    protected function get_legacy_logdata() {
        $attempt = $this->get_record_snapshot('quiz_attempts', $this->objectid);

        return array($this->courseid, 'quiz', 'attempt', 'review.php?attempt=' . $this->objectid,
            $attempt->quiz, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'quiz_attempts', 'restore' => 'quiz_attempt');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['quizid'] = array('db' => 'quiz', 'restore' => 'quiz');

        return $othermapped;
    }
}
