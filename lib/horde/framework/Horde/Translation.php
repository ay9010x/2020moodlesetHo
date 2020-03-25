<?php



abstract class Horde_Translation
{
    
    static protected $_domain;

    
    static protected $_directory;

    
    static protected $_handlers = array();

    
    static public function loadHandler($handlerClass)
    {
        if (!self::$_domain || !self::$_directory) {
            throw new Horde_Translation_Exception('The domain and directory properties must be set by the class that extends Horde_Translation.');
        }
        self::setHandler(self::$_domain, new $handlerClass(self::$_domain, self::$_directory));
    }

    
    static public function setHandler($domain, $handler)
    {
        self::$_handlers[$domain] = $handler;
    }

    
    static public function t($message)
    {
        if (!isset(self::$_handlers[self::$_domain])) {
            self::loadHandler('Horde_Translation_Handler_Gettext');
        }
        return self::$_handlers[self::$_domain]->t($message);
    }

    
    static public function ngettext($singular, $plural, $number)
    {
        if (!isset(self::$_handlers[self::$_domain])) {
            self::loadHandler('Horde_Translation_Handler_Gettext');
        }
        return self::$_handlers[self::$_domain]->ngettext($singular, $plural, $number);
    }

    
    static public function r($message)
    {
        return $message;
    }

}
