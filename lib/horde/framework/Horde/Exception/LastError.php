<?php

class Horde_Exception_LastError extends Horde_Exception
{
    
    public function __construct($message = null, $code_or_lasterror = null)
    {
        if (is_array($code_or_lasterror)) {
            if ($message) {
                $message .= $code_or_lasterror['message'];
            } else {
                $message = $code_or_lasterror['message'];
            }
            parent::__construct($message, $code_or_lasterror['type']);
            $this->file = $code_or_lasterror['file'];
            $this->line = $code_or_lasterror['line'];
        } else {
            parent::__construct($message, $code_or_lasterror);
        }
    }

}
