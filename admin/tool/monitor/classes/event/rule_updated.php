<?php



namespace tool_monitor\event;

defined('MOODLE_INTERNAL') || die();


class rule_updated extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'tool_monitor_rules';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventruleupdated', 'tool_monitor');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the event monitor rule with id '$this->objectid'.";
    }

    
    public function get_url() {
        return new \moodle_url('/admin/tool/monitor/edit.php', array('ruleid' => $this->objectid,
            'courseid' => $this->courseid));
    }

    public static function get_objectid_mapping() {
                return array('db' => 'tool_monitor_rules', 'restore' => \core\event\base::NOT_MAPPED);
    }
}
