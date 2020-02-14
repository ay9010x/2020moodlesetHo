<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class essay_assessed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'lesson_grades';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has marked the essay with id '{$this->other['attemptid']}' and " .
            "recorded a mark '$this->objectid' in the lesson with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        $lesson = $this->get_record_snapshot('lesson', $this->other['lessonid']);
        return array($this->courseid, 'lesson', 'update grade', 'essay.php?id=' .
                $this->contextinstanceid, $lesson->name, $this->contextinstanceid);
    }

    
    public static function get_name() {
        return get_string('eventessayassessed', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/essay.php', array('id' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (!isset($this->other['lessonid'])) {
            throw new \coding_exception('The \'lessonid\' value must be set in other.');
        }
        if (!isset($this->other['attemptid'])) {
            throw new \coding_exception('The \'attemptid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson_grades', 'restore' => 'lesson_grade');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['lessonid'] = array('db' => 'lesson', 'restore' => 'lesson');
        $othermapped['attemptid'] = array('db' => 'lesson_attempts', 'restore' => 'lesson_attept');

        return $othermapped;
    }
}
