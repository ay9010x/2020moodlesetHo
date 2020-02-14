<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class lesson_restarted extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventlessonrestarted', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/view.php', array('id' => $this->contextinstanceid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' abandoned their previous incomplete attempt ".
               "and started a new attempt on the lesson with course module id '$this->contextinstanceid'.";
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson', 'restore' => 'lesson');
    }
}
