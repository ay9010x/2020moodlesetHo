<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class user_override_updated extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson_overrides';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventoverrideupdated', 'mod_lesson');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the override with id '$this->objectid' for the lesson with " .
            "course module id '$this->contextinstanceid' for the user with id '{$this->relateduserid}'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/overrideedit.php', array('id' => $this->objectid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }

        if (!isset($this->other['lessonid'])) {
            throw new \coding_exception('The \'lessonid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson_overrides', 'restore' => 'lesson_override');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['lessonid'] = array('db' => 'lesson', 'restore' => 'lesson');

        return $othermapped;
    }
}