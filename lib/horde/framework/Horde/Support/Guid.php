<?php

class Horde_Support_Guid
{
    
    private $_guid;

    
    public function __construct(array $opts = array())
    {
        $this->generate($opts);
    }

    
    public function generate(array $opts = array())
    {
        $this->_guid = date('YmdHis')
            . '.'
            . (isset($opts['prefix']) ? $opts['prefix'] . '.' : '')
            . strval(new Horde_Support_Randomid())
            . '@'
            . (isset($opts['server']) ? $opts['server'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost'));
    }

    
    public function __toString()
    {
        return $this->_guid;
    }

}
