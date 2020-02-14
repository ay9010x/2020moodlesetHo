<?php



namespace mod_workshop\event;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->dirroot/mod/workshop/locallib.php");


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'workshop';
    }

    
    public static function get_legacy_eventname() {
        return 'workshop_viewed';
    }

    
    protected function get_legacy_eventdata() {
        global $USER;

        $workshop = $this->get_record_snapshot('workshop', $this->objectid);
        $course   = $this->get_record_snapshot('course', $this->courseid);
        $cm       = $this->get_record_snapshot('course_modules', $this->contextinstanceid);
        $workshop = new \workshop($workshop, $cm, $course);
        return (object)array('workshop' => $workshop, 'user' => $USER);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'workshop', 'restore' => 'workshop');
    }
}
