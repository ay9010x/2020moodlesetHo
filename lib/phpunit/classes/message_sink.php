<?php





class phpunit_message_sink {
    
    protected $messages = array();

    
    public function close() {
        phpunit_util::stop_message_redirection();
    }

    
    public function add_message($message) {
        
        $this->messages[] = $message;
    }

    
    public function get_messages() {
        return $this->messages;
    }

    
    public function count() {
        return count($this->messages);
    }

    
    public function clear() {
        $this->messages = array();
    }
}
