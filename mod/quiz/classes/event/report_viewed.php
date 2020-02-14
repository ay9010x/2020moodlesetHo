<?php



namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class report_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventreportviewed', 'mod_quiz');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the report '" . s($this->other['reportname']) . "' for the quiz with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/report.php', array('id' => $this->contextinstanceid,
            'mode' => $this->other['reportname']));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quiz', 'report', 'report.php?id=' . $this->contextinstanceid . '&mode=' .
            $this->other['reportname'], $this->other['quizid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['quizid'])) {
            throw new \coding_exception('The \'quizid\' value must be set in other.');
        }

        if (!isset($this->other['reportname'])) {
            throw new \coding_exception('The \'reportname\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['quizid'] = array('db' => 'quiz', 'restore' => 'quiz');

        return $othermapped;
    }
}
