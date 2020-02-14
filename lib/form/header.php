<?php




require_once 'HTML/QuickForm/header.php';


class MoodleQuickForm_header extends HTML_QuickForm_header
{
    
    var $_helpbutton='';

    
    public function __construct($elementName = null, $text = null) {
        parent::__construct($elementName, $text);
    }

    
    public function MoodleQuickForm_header($elementName = null, $text = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $text);
    }

   
    function accept(&$renderer, $required=false, $error=null)
    {
        $this->_text .= $this->getHelpButton();
        $renderer->renderHeader($this);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }
}