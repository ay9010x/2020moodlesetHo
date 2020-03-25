<?php





class phpunit_phpmailer_sink {
    
    protected $messages = array();

    
    public function close() {
        phpunit_util::stop_phpmailer_redirection();
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
