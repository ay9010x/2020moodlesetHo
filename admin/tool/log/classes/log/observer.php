<?php



namespace tool_log\log;

defined('MOODLE_INTERNAL') || die();

class observer {
    
    public static function store(\core\event\base $event) {
        $logmanager = get_log_manager();
        if (get_class($logmanager) === 'tool_log\log\manager') {
            
            $logmanager->process($event);
        }
    }
}
