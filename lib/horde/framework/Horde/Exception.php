<?php

class Horde_Exception extends Exception
{
    
    public $details;

    
    public $logged = false;

    
    protected $_logLevel = 0;

    
    public function getLogLevel()
    {
        return $this->_logLevel;
    }

    
    public function setLogLevel($level = 0)
    {
        if (is_string($level)) {
            $level = defined('Horde_Log::' . $level)
                ? constant('Horde_Log::' . $level)
                : 0;
        }

        $this->_logLevel = $level;
    }

}
