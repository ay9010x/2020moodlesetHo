<?php



namespace mod_scorm\event;
defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'scorm';
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'scorm', 'pre-view', 'view.php?id=' . $this->contextinstanceid, $this->objectid,
                $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'scorm', 'restore' => 'scorm');
    }
}

