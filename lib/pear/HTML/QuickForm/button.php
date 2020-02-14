<?php


require_once("HTML/QuickForm/input.php");


class HTML_QuickForm_button extends HTML_QuickForm_input
{
    
    
    public function __construct($elementName=null, $value=null, $attributes=null) {
        parent::__construct($elementName, null, $attributes);
        $this->_persistantFreeze = false;
        $this->setValue($value);
        $this->setType('button');
    } 
    
    public function HTML_QuickForm_button($elementName=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $value, $attributes);
    }
    
        
    
    function freeze()
    {
        return false;
    } 
     
} ?>
