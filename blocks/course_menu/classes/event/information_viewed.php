<?php

namespace block_course_menu\event;
defined('MOODLE_INTERNAL') || die();

class information_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'course';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' viewed course information with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventinformationviewed', 'block_course_menu');
    }

    
    public function get_url() {
        return new \moodle_url('/blocks/course_menu/information.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'course_menu', 'blocks', 'information.php?id=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }
}
