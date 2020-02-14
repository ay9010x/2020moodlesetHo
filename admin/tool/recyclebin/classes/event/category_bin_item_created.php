<?php



namespace tool_recyclebin\event;

defined('MOODLE_INTERNAL') || die();


class category_bin_item_created extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'tool_recyclebin_category';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventitemcreated', 'tool_recyclebin');
    }

    
    public function get_description() {
        return get_string('eventitemcreated_desc', 'tool_recyclebin', array(
            'objectid' => $this->objectid
        ));
    }
}
