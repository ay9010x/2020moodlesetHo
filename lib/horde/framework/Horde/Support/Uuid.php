<?php

class Horde_Support_Uuid
{
    
    private $_uuid;

    
    public function __construct()
    {
        $this->generate();
    }

    
    public function generate()
    {
        $this->_uuid = null;
        if (extension_loaded('uuid')) {
            if (function_exists('uuid_export')) {
                                if (uuid_create($ctx) == UUID_RC_OK &&
                    uuid_make($ctx, UUID_MAKE_V4) == UUID_RC_OK &&
                    uuid_export($ctx, UUID_FMT_STR, $str) == UUID_RC_OK) {
                    $this->_uuid = $str;
                    uuid_destroy($ctx);
                }
            } else {
                                $this->_uuid = uuid_create();
            }
        }
        if (!$this->_uuid) {
            list($time_mid, $time_low) = explode(' ', microtime());
            $time_low = (int)$time_low;
            $time_mid = (int)substr($time_mid, 2) & 0xffff;
            $time_high = mt_rand(0, 0x0fff) | 0x4000;

            $clock = mt_rand(0, 0x3fff) | 0x8000;

            $node_low = function_exists('zend_thread_id')
                ? zend_thread_id()
                : getmypid();
            $node_high = isset($_SERVER['SERVER_ADDR'])
                ? ip2long($_SERVER['SERVER_ADDR'])
                : crc32(php_uname());
            $node = bin2hex(pack('nN', $node_low, $node_high));

            $this->_uuid = sprintf('%08x-%04x-%04x-%04x-%s',
                $time_low, $time_mid, $time_high, $clock, $node);
        }
    }

    
    public function __toString()
    {
        return $this->_uuid;
    }

}
