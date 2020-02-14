<?php

class Horde_Support_Memory
{
    
    protected $_start = array();

    
    protected $_idx = 0;

    
    public function push()
    {
        $start = $this->_start[$this->_idx++] = array(
            memory_get_usage(),
            memory_get_peak_usage(),
            memory_get_usage(true),
            memory_get_peak_usage(true)
        );
        return $start;
    }

    
    public function pop()
    {
        if (! ($this->_idx > 0)) {
            throw new Exception('No timers have been started');
        }
        $start = $this->_start[--$this->_idx];
        return array(
            memory_get_usage() - $start[0],
            memory_get_peak_usage() - $start[1],
            memory_get_usage(true) - $start[2],
            memory_get_peak_usage(true) - $start[3]
        );
    }

}
