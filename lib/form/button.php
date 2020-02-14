<?php




require_once("HTML/QuickForm/button.php");


class MoodleQuickForm_button extends HTML_QuickForm_button
{
    
    var $_helpbutton='';

    
    public function __construct($elementName=null, $value=null, $attributes=null) {
        parent::__construct($elementName, $value, $attributes);
    }

    
    public function MoodleQuickForm_button($elementName=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $value, $attributes);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'nodisplay';
        } else {
            return 'default';
        }
    }
}
