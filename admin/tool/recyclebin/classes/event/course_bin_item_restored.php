<?php



namespace tool_recyclebin\event;

defined('MOODLE_INTERNAL') || die();


class course_bin_item_restored extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'tool_recyclebin_course';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventitemrestored', 'tool_recyclebin');
    }

    
    public function get_description() {
        return get_string('eventitemrestored_desc', 'tool_recyclebin', array(
            'objectid' => $this->objectid
        ));
    }
}
