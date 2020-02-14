<?php

class Horde_Support_Randomid
{
    
    private $_id;

    
    public function __construct()
    {
        $this->_id = $this->generate();
    }

    
    public function generate()
    {
        $r = mt_rand();

        $elts = array(
            $r,
            uniqid(),
            getmypid()
        );
        if (function_exists('zend_thread_id')) {
            $elts[] = zend_thread_id();
        }
        if (function_exists('sys_getloadavg') &&
            $loadavg = sys_getloadavg()) {
            $elts = array_merge($elts, $loadavg);
        }

        shuffle($elts);

        
        return substr(str_replace(
            array('/', '+', '='),
            array('-', '_', ''),
            base64_encode(pack('H*', hash('md5', implode('', $elts))))
        ) . $r, 0, 23);
    }

    
    public function __toString()
    {
        return $this->_id;
    }
}
