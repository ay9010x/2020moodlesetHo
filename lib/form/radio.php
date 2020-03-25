<?php




require_once('HTML/QuickForm/radio.php');


class MoodleQuickForm_radio extends HTML_QuickForm_radio{
    
    var $_helpbutton='';

    
    public function __construct($elementName=null, $elementLabel=null, $text=null, $value=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $text, $value, $attributes);
    }

    
    public function MoodleQuickForm_radio($elementName=null, $elementLabel=null, $text=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $text, $value, $attributes);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }

    
    function getFrozenHtml()
    {
        $output = '<input type="radio" disabled="disabled" id="'.$this->getAttribute('id').'" ';
        if ($this->getChecked()) {
            $output .= 'checked="checked" />'.$this->_getPersistantData();
        } else {
            $output .= '/>';
        }
        return $output;
    }

    
    function toHtml()
    {
        return '<span>' . parent::toHtml() . '</span>';
    }
}
