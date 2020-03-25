<?php



namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class user_override_updated extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'quiz_overrides';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventoverrideupdated', 'mod_quiz');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the override with id '$this->objectid' for the quiz with " .
            "course module id '$this->contextinstanceid' for the user with id '{$this->relateduserid}'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/overrideedit.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quiz', 'edit override', 'overrideedit.php?id=' . $this->objectid, $this->other['quizid'],
            $this->contextinstanceid);
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
        return array('db' => 'quiz_overrides', 'restore' => 'quiz_override');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['quizid'] = array('db' => 'quiz', 'restore' => 'quiz');

        return $othermapped;
    }
}
