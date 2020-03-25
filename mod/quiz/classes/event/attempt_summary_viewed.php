<?php



namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class attempt_summary_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'quiz_attempts';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventattemptsummaryviewed', 'mod_quiz');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the summary for the attempt with id '$this->objectid' belonging " .
            "to the user with id '$this->relateduserid' for the quiz with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/summary.php', array('attempt' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quiz', 'view summary', 'summary.php?attempt=' . $this->objectid,
            $this->other['quizid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['quizid'])) {
            throw new \coding_exception('The \'quizid\' must be set in other.');
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
