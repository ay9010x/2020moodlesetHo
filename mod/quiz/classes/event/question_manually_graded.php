<?php


namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class question_manually_graded extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'question';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventquestionmanuallygraded', 'mod_quiz');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' manually graded the question with id '$this->objectid' for the attempt " .
            "with id '{$this->other['attemptid']}' for the quiz with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/comment.php', array('attempt' => $this->other['attemptid'],
            'slot' => $this->other['slot']));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quiz', 'manualgrade', 'comment.php?attempt=' . $this->other['attemptid'] .
            '&slot=' . $this->other['slot'], $this->other['quizid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['quizid'])) {
            throw new \coding_exception('The \'quizid\' value must be set in other.');
        }

        if (!isset($this->other['attemptid'])) {
            throw new \coding_exception('The \'attemptid\' value must be set in other.');
        }

        if (!isset($this->other['slot'])) {
            throw new \coding_exception('The \'slot\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'question', 'restore' => 'question');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['quizid'] = array('db' => 'quiz', 'restore' => 'quiz');
        $othermapped['attemptid'] = array('db' => 'quiz_attempts', 'restore' => 'quiz_attempt');

        return $othermapped;
    }
}
