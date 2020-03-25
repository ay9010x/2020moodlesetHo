<?php


require_once("HTML/QuickForm/input.php");


class HTML_QuickForm_text extends HTML_QuickForm_input
{
                
    
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->setType('text');
    } 
    
    public function HTML_QuickForm_text($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }
        
        
    
    function setSize($size)
    {
        $this->updateAttributes(array('size'=>$size));
    } 
        
    
    function setMaxlength($maxlength)
    {
        $this->updateAttributes(array('maxlength'=>$maxlength));
    } 
        
} ?>
