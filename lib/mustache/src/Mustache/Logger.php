<?php




interface Mustache_Logger
{
    
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    
    public function emergency($message, array $context = array());

    
    public function alert($message, array $context = array());

    
    public function critical($message, array $context = array());

    
    public function error($message, array $context = array());

    
    public function warning($message, array $context = array());

    
    public function notice($message, array $context = array());

    
    public function info($message, array $context = array());

    
    public function debug($message, array $context = array());

    
    public function log($level, $message, array $context = array());
}