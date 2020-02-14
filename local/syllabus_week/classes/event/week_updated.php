<?php

namespace local_syllabus_week\event;
defined('MOODLE_INTERNAL') || die();

class week_updated extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'syllabus_week';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated course syllabus week with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventsyllabusweekupdated', 'local_syllabus_week');
    }

    
    public function get_url() {
        return new \moodle_url('/local/syllabus_week/edit.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'syllabus_week', 'week updated', $this->get_url(),
            $this->objectid, $this->contextinstanceid);
    }
}