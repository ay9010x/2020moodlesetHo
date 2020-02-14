<?php



namespace core\lock;

defined('MOODLE_INTERNAL') || die();


interface lock_factory {

    
    public function __construct($type);

    
    public function supports_timeout();

    
    public function supports_auto_release();

    
    public function supports_recursion();

    
    public function is_available();

    
    public function get_lock($resource, $timeout, $maxlifetime = 86400);

    
    public function release_lock(lock $lock);

    
    public function extend_lock(lock $lock, $maxlifetime = 86400);
}
