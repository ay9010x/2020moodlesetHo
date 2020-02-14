<?php



namespace mod_glossary\event;
defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'glossary';
    }

    
    public function get_url() {
        $params = array('id' => $this->contextinstanceid);
        if (!empty($this->other['mode'])) {
            $params['mode'] = $this->other['mode'];
        }
        return new \moodle_url("/mod/$this->objecttable/view.php", $params);
    }

    
    public function get_legacy_logdata() {
                return array($this->courseid, $this->objecttable, 'view',
            'view.php?id=' . $this->contextinstanceid . '&amp;tab=-1',
            $this->objectid, $this->contextinstanceid);
    }

    public static function get_objectid_mapping() {
        return array('db' => 'glossary', 'restore' => 'glossary');
    }

    public static function get_other_mapping() {
                return false;
    }
}
