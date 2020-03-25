<?php



namespace core\session;

defined('MOODLE_INTERNAL') || die();


abstract class util {
    
    public static function connection_string_to_memcache_servers($str) {
        $servers = array();
        $parts   = explode(',', $str);
        foreach ($parts as $part) {
            $part = trim($part);
            $pos  = strrpos($part, ':');
            if ($pos !== false) {
                $host = substr($part, 0, $pos);
                $port = substr($part, ($pos + 1));
            } else {
                $host = $part;
                $port = 11211;
            }
            $servers[] = array($host, $port);
        }
        return $servers;
    }
}
