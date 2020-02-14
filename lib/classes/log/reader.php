<?php



namespace core\log;

defined('MOODLE_INTERNAL') || die();

interface reader {
    
    public function get_name();

    
    public function get_description();

    
    public function is_logging();
}
