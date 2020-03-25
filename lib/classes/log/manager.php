<?php



namespace core\log;

defined('MOODLE_INTERNAL') || die();


interface manager {
    
    public function get_readers($interface = null);

    
    public function dispose();

    
    public function get_supported_logstores($component);
}
