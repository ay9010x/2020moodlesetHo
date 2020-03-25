<?php


require_once("HTML/QuickForm/input.php");


class HTML_QuickForm_reset extends HTML_QuickForm_input
{
        
    
    public function __construct($elementName=null, $value=null, $attributes=null) {
        parent::__construct($elementName, null, $attributes);
        $this->setValue($value);
        $this->setType('reset');
    } 
    
    public function HTML_QuickForm_reset($elementName=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $value, $attributes);
    }

        
    
    function freeze()
    {
        return false;
    } 
    
} ?>
