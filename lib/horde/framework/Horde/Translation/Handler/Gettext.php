<?php



class Horde_Translation_Handler_Gettext implements Horde_Translation_Handler
{
    
    protected $_domain;

    
    protected $_gettext;

    
    public function __construct($domain, $path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException("$path is not a directory");
        }
        $this->_gettext = function_exists('_');
        if (!$this->_gettext) {
            return;
        }
        $this->_domain = $domain;
        bindtextdomain($this->_domain, $path);
    }

    
    public function t($message)
    {
        return $this->_gettext ? dgettext($this->_domain, $message) : $message;
    }

    
    public function ngettext($singular, $plural, $number)
    {
        return $this->_gettext
          ? dngettext($this->_domain, $singular, $plural, $number)
          : ($number > 1 ? $plural : $singular);
    }
}
