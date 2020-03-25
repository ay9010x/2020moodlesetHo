<?php


namespace core\task;


abstract class task_base {

    
    private $lock = null;

    
    private $cronlock = null;

    
    private $component = '';

    
    private $blocking = false;

    
    private $faildelay = 0;

    
    private $nextruntime = 0;

    
    public function set_lock(\core\lock\lock $lock) {
        $this->lock = $lock;
    }

    
    public function set_cron_lock(\core\lock\lock $lock) {
        $this->cronlock = $lock;
    }

    
    public function get_lock() {
        return $this->lock;
    }

    
    public function get_next_run_time() {
        return $this->nextruntime;
    }

    
    public function set_next_run_time($nextruntime) {
        $this->nextruntime = $nextruntime;
    }

    
    public function get_cron_lock() {
        return $this->cronlock;
    }

    
    public function set_blocking($blocking) {
        $this->blocking = $blocking;
    }

    
    public function is_blocking() {
        return $this->blocking;
    }

    
    public function set_component($component) {
        $this->component = $component;
    }

    
    public function get_component() {
        return $this->component;
    }

    
    public function set_fail_delay($faildelay) {
        $this->faildelay = $faildelay;
    }

    
    public function get_fail_delay() {
        return $this->faildelay;
    }

    
    public abstract function execute();
}
