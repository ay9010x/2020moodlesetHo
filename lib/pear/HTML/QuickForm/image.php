<?php

require_once("HTML/QuickForm/input.php");


class HTML_QuickForm_image extends HTML_QuickForm_input
{
    
    
    public function __construct($elementName=null, $src='', $attributes=null) {
        parent::__construct($elementName, null, $attributes);
        $this->setType('image');
        $this->setSource($src);
    } 
    
    public function HTML_QuickForm_image($elementName=null, $src='', $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $src, $attributes);
    }

        
    
    function setSource($src)
    {
        $this->updateAttributes(array('src' => $src));
    } 
        
    
    function setBorder($border)
    {
        $this->updateAttributes(array('border' => $border));
    } 
        
    
    function setAlign($align)
    {
        $this->updateAttributes(array('align' => $align));
    } 
        
    
    function freeze()
    {
        return false;
    } 
    
} ?>
