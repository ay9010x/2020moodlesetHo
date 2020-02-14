<?php

class Horde_Support_Stack
{
    
    protected $_stack = array();

    public function __construct($stack = array())
    {
        $this->_stack = $stack;
    }

    public function push($value)
    {
        $this->_stack[] = $value;
    }

    public function pop()
    {
        return array_pop($this->_stack);
    }

    public function peek($offset = 1)
    {
        if (isset($this->_stack[count($this->_stack) - $offset])) {
            return $this->_stack[count($this->_stack) - $offset];
        } else {
            return null;
        }
    }
}
