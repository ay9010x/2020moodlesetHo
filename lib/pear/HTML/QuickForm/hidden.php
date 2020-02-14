<?php


require_once("HTML/QuickForm/input.php");


class HTML_QuickForm_hidden extends HTML_QuickForm_input
{
    
    
    public function __construct($elementName=null, $value='', $attributes=null) {
        parent::__construct($elementName, null, $attributes);
        $this->setType('hidden');
        $this->setValue($value);
    } 
    
    public function HTML_QuickForm_hidden($elementName=null, $value='', $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        return self::__construct($elementName, $value, $attributes);
    }

        
    
    function freeze()
    {
        return false;
    } 
        
   
    function accept(&$renderer, $required=false, $error=null)
    {
        $renderer->renderHidden($this);
    } 
    
} ?>
