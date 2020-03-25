<?php



namespace tool_log\log;

defined('MOODLE_INTERNAL') || die();

interface writer extends store {
    
    public function write(\core\event\base $event);
}
