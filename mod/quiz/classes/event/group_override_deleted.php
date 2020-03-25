<?php


namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class group_override_deleted extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'quiz_overrides';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventoverridedeleted', 'mod_quiz');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the override with id '$this->objectid' for the quiz with " .
            "course module id '$this->contextinstanceid' for the group with id '{$this->other['groupid']}'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/quiz/overrides.php', array('cmid' => $this->contextinstanceid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'quiz', 'delete override', 'overrides.php?cmid=' . $this->contextinstanceid,
            $this->other['quizid'], $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['quizid'])) {
            throw new \coding_exception('The \'quizid\' value must be set in other.');
        }

        if (!isset($this->other['groupid'])) {
            throw new \coding_exception('The \'groupid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'quiz_overrides', 'restore' => 'quiz_override');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['quizid'] = array('db' => 'quiz', 'restore' => 'quiz');
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;
    }
}
