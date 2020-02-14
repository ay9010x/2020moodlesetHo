<?php


namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class attempt_deleted extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'quiz_attempts';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventattemptdeleted', 'mod_quiz');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the attempt with id '$this->objectid' belonging to the quiz " .
            "with course module id '$this->contextinstanceid' for the user with id '$this->relateduserid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/report.php', array('id' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quiz', 'delete attempt', 'report.php?id=' . $this->contextinstanceid,
            $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['quizid'])) {
            throw new \coding_exception('The \'quizid\' value must be set in other.');
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
