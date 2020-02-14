<?php


class HTMLPurifier_AttrDef_HTML_Bool extends HTMLPurifier_AttrDef
{

    
    protected $name;

    
    public $minimized = true;

    
    public function __construct($name = false)
    {
        $this->name = $name;
    }

    
    public function validate($string, $config, $context)
    {
        return $this->name;
    }

    
    public function make($string)
    {
        return new HTMLPurifier_AttrDef_HTML_Bool($string);
    }
}

