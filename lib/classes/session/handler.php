<?php



namespace core\session;

defined('MOODLE_INTERNAL') || die();


abstract class handler {
    
    public function start() {
        return session_start();
    }

    
    public abstract function init();

    
    public abstract function session_exists($sid);

    
    public abstract function kill_all_sessions();

    
    public abstract function kill_session($sid);
}
