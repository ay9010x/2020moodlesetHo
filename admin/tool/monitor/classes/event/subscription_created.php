<?php



namespace tool_monitor\event;

defined('MOODLE_INTERNAL') || die();


class subscription_created extends \core\event\base {

    
    protected function init() {
        $this->data['objecttable'] = 'tool_monitor_subscriptions';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    
    public static function get_name() {
        return get_string('eventsubcreated', 'tool_monitor');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' created the event monitor subscription with id '$this->objectid'.";
    }

    public static function get_objectid_mapping() {
                return array('db' => 'tool_monitor_subscriptions', 'restore' => \core\event\base::NOT_MAPPED);
    }
}
