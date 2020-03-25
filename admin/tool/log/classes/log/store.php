<?php



namespace tool_log\log;

defined('MOODLE_INTERNAL') || die();

interface store {
    
    public function __construct(\tool_log\log\manager $manager);

    
    public function dispose();
}
