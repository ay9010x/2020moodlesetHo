<?php




defined('MOODLE_INTERNAL') || die();


interface workshop_allocator {

    
    public function init();

    
    public function ui();

    
    public static function delete_instance($workshopid);
}



class workshop_allocation_result implements renderable {

    
    const STATUS_VOID           = 0;
    
    const STATUS_EXECUTED       = 1;
    
    const STATUS_FAILED         = 2;
    
    const STATUS_CONFIGURED     = 3;

    
    protected $allocator;
    
    protected $status = null;
    
    protected $message = null;
    
    protected $timestart = null;
    
    protected $timeend = null;
    
    protected $logs = array();

    
    public function __construct(workshop_allocator $allocator) {
        $this->allocator = $allocator;
        $this->timestart = time();
    }

    
    public function set_status($status, $message = null) {
        $this->status = $status;
        $this->message = is_null($message) ? $this->message : $message;
        $this->timeend = time();
    }

    
    public function get_status() {
        return $this->status;
    }

    
    public function get_message() {
        return $this->message;
    }

    
    public function get_timeend() {
        return $this->timeend;
    }

    
    public function log($message, $type = 'ok', $indent = 0) {
        $log = new stdClass();
        $log->message = $message;
        $log->type = $type;
        $log->indent = $indent;

        $this->logs[] = $log;
    }

    
    public function get_logs() {
        return $this->logs;
    }
}
