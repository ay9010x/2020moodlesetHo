<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class essay_attempt_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson_attempts';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventessayattemptviewed', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/essay.php', array('id' => $this->contextinstanceid,
            'mode' => 'grade', 'attemptid' =>  $this->objectid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the essay grade for the user with id '$this->relateduserid' for " .
            "the attempt with id '$this->objectid' for the lesson activity with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'lesson', 'view grade', 'essay.php?id=' . $this->contextinstanceid . '&mode=grade&attemptid='
            . $this->objectid, get_string('manualgrading', 'lesson'), $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson_attempts', 'restore' => 'lesson_attempt');
    }
}
