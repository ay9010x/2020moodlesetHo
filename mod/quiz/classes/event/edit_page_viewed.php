<?php



namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class edit_page_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventeditpageviewed', 'mod_quiz');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the edit page for the quiz with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/edit.php', array('cmid' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quiz', 'editquestions', 'view.php?id=' . $this->contextinstanceid,
            $this->other['quizid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['quizid'])) {
            throw new \coding_exception('The \'quizid\' value must be set in other.');
        }
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['quizid'] = array('db' => 'quiz', 'restore' => 'quiz');

        return $othermapped;
    }
}
