<?php



namespace mod_forum\event;

defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'forum';
    }

    
    public function get_url() {
        return new \moodle_url('/mod/forum/view.php', array('f' => $this->objectid));
    }

    
    protected function get_legacy_logdata() {
        return array($this->courseid, 'forum', 'view forum', 'view.php?f=' . $this->objectid,
            $this->objectid, $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'forum', 'restore' => 'forum');
    }
}

