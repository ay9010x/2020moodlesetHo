<?php


require_once("HTML/QuickForm/input.php");


class HTML_QuickForm_submit extends HTML_QuickForm_input
{
    
    
    public function __construct($elementName=null, $value=null, $attributes=null) {
        parent::__construct($elementName, null, $attributes);
        $this->setValue($value);
        $this->setType('submit');
    } 
    
    public function HTML_QuickForm_submit($elementName=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $value, $attributes);
    }

        
    
    function freeze()
    {
        return false;
    } 
        
   
    function exportValue(&$submitValues, $assoc = false)
    {
        return $this->_prepareValue($this->_findValue($submitValues), $assoc);
    }

    } ?>
