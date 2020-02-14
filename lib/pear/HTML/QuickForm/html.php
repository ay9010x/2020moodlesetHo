<?php


require_once 'HTML/QuickForm/static.php';


class HTML_QuickForm_html extends HTML_QuickForm_static
{
    
   
    public function __construct($text = null) {
        parent::__construct(null, null, $text);
        $this->_type = 'html';
    }

    
    public function HTML_QuickForm_html($text = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($text);
    }

        
   
    function accept(&$renderer, $required=false, $error=null)
    {
        $renderer->renderHtml($this);
    } 
    
} ?>
