<?php




require_once("HTML/QuickForm/static.php");


class MoodleQuickForm_static extends HTML_QuickForm_static{
    
    var $_elementTemplateType='static';

    
    var $_helpbutton='';

    
    public function __construct($elementName=null, $elementLabel=null, $text=null) {
        parent::__construct($elementName, $elementLabel, $text);
    }

    
    public function MoodleQuickForm_static($elementName=null, $elementLabel=null, $text=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $text);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function getElementTemplateType(){
        return $this->_elementTemplateType;
    }
}
