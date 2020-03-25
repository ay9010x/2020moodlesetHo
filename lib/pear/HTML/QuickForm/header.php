<?php


require_once 'HTML/QuickForm/static.php';


class HTML_QuickForm_header extends HTML_QuickForm_static
{
    
   
    public function __construct($elementName = null, $text = null) {
        parent::__construct($elementName, null, $text);
        $this->_type = 'header';
    }

    
    public function HTML_QuickForm_header($elementName = null, $text = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $text);
    }

        
   
    function accept(&$renderer, $required=false, $error=null)
    {
        $renderer->renderHeader($this);
    } 
    
} ?>
