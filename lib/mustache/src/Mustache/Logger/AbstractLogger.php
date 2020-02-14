<?php




abstract class Mustache_Logger_AbstractLogger implements Mustache_Logger
{
    
    public function emergency($message, array $context = array())
    {
        $this->log(Mustache_Logger::EMERGENCY, $message, $context);
    }

    
    public function alert($message, array $context = array())
    {
        $this->log(Mustache_Logger::ALERT, $message, $context);
    }

    
    public function critical($message, array $context = array())
    {
        $this->log(Mustache_Logger::CRITICAL, $message, $context);
    }

    
    public function error($message, array $context = array())
    {
        $this->log(Mustache_Logger::ERROR, $message, $context);
    }

    
    public function warning($message, array $context = array())
    {
        $this->log(Mustache_Logger::WARNING, $message, $context);
    }

    
    public function notice($message, array $context = array())
    {
        $this->log(Mustache_Logger::NOTICE, $message, $context);
    }

    
    public function info($message, array $context = array())
    {
        $this->log(Mustache_Logger::INFO, $message, $context);
    }

    
    public function debug($message, array $context = array())
    {
        $this->log(Mustache_Logger::DEBUG, $message, $context);
    }
}
