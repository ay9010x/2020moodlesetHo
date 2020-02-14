<?php



namespace mod_quiz\event;

defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'quiz';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'quiz', 'restore' => 'quiz');
    }
}
