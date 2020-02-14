<?php



class Minify_Logger {

    
    public static function setLogger($obj = null) {
        self::$_logger = $obj
            ? $obj
            : null;
    }
    
    
    public static function log($msg, $label = 'Minify') {
        if (! self::$_logger) return;
        self::$_logger->log($msg, $label);
    }
    
    
    private static $_logger = null;
}
