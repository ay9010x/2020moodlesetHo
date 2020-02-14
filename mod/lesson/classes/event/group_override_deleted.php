<?php


namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class group_override_deleted extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson_overrides';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventoverridedeleted', 'mod_lesson');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the override with id '$this->objectid' for the lesson with " .
            "course module id '$this->contextinstanceid' for the group with id '{$this->other['groupid']}'.";
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/overrides.php', array('cmid' => $this->contextinstanceid));
    }

    
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['lessonid'])) {
            throw new \coding_exception('The \'lessonid\' value must be set in other.');
        }

        if (!isset($this->other['groupid'])) {
            throw new \coding_exception('The \'groupid\' value must be set in other.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson_overrides', 'restore' => 'lesson_override');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['lessonid'] = array('db' => 'lesson', 'restore' => 'lesson');
        $othermapped['groupid'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;
    }
}
