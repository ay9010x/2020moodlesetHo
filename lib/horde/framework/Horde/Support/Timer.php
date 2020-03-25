<?php

class Horde_Support_Timer
{
    
    protected $_start = array();

    
    protected $_idx = 0;

    
    public function push()
    {
        $start = $this->_start[$this->_idx++] = microtime(true);
        return $start;
    }

    
    public function pop()
    {
        $etime = microtime(true);

        if (! ($this->_idx > 0)) {
            throw new Exception('No timers have been started');
        }

        return $etime - $this->_start[--$this->_idx];
    }

}
