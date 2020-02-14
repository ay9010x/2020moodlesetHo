<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class course_module_viewed extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wiki';
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki', 'restore' => 'wiki');
    }
}
