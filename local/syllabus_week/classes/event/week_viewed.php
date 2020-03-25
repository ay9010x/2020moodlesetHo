<?php

namespace local_syllabus_week\event;
defined('MOODLE_INTERNAL') || die();

class week_viewed extends \core\event\base {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'syllabus_week';
    }

    
    public function get_description() {
        return 'User with id ' . $this->userid . ' viewed course syllabus week with instanceid ' .
            $this->objectid;
    }

    
    public static function get_name() {
        return get_string('eventsyllabusweekviewed', 'local_syllabus_week');
    }

    
    public function get_url() {
        return new \moodle_url('/local/syllabus_week/index.php', array('id' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'syllabus_week', 'local', 'index.php?id=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }
}