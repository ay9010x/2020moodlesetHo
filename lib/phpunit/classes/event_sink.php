<?php





class phpunit_event_sink {
    
    protected $events = array();

    
    public function close() {
        phpunit_util::stop_event_redirection();
    }

    
    public function add_event(\core\event\base $event) {
        
        $this->events[] = $event;
    }

    
    public function get_events() {
        return $this->events;
    }

    
    public function count() {
        return count($this->events);
    }

    
    public function clear() {
        $this->events = array();
    }
}
