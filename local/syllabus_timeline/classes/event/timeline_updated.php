<?php

namespace local_syllabus_timeline\event;
defined('MOODLE_INTERNAL') || die();

class timeline_updated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'syllabus_timeline';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated course syllabus timeline with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventsyllabustimelineupdated', 'local_syllabus_timeline');
    }

    
    public function get_url() {
        return new \moodle_url('/local/syllabus_timeline/edit.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'syllabus_timeline', 'timeline updated', $this->get_url(),
            $this->objectid, $this->contextinstanceid);
    }
}