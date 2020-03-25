<?php

namespace local_syllabus_timeline\event;
defined('MOODLE_INTERNAL') || die();

class timeline_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'syllabus_timeline';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' viewed course syllabus timeline with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventsyllabustimelineviewed', 'local_syllabus_timeline');
    }

    
    public function get_url() {
        return new \moodle_url('/local/syllabus_timeline/index.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'syllabus_timeline', 'local', 'index.php?id=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }
}