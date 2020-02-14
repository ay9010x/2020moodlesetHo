<?php



namespace tool_recyclebin\event;

defined('MOODLE_INTERNAL') || die();


class course_bin_item_deleted extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'tool_recyclebin_course';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventitemdeleted', 'tool_recyclebin');
    }

    
    public function get_description() {
        return get_string('eventitemdeleted_desc', 'tool_recyclebin', array(
            'objectid' => $this->objectid
        ));
    }
}
