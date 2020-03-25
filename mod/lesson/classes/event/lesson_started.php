<?php



namespace mod_lesson\event;

defined('MOODLE_INTERNAL') || die();


class lesson_started extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'lesson';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    
    public static function get_name() {
        return get_string('eventlessonstarted', 'mod_lesson');
    }

    
    public function get_url() {
        return new \moodle_url('/mod/lesson/view.php', array('id' => $this->contextinstanceid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' started the lesson with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'lesson', 'start', 'view.php?id=' . $this->contextinstanceid,
            $this->objectid, $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'lesson', 'restore' => 'lesson');
    }
}
