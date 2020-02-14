<?php


namespace mod_resource\event;

defined('MOODLE_INTERNAL') || die();


class course_module_downloaded extends \core\event\course_module_viewed {

    
    protected function init() {
        $this->data['objecttable'] = 'resource';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
}