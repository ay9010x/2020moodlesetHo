<?php




require_once("HTML/QuickForm/group.php");


class MoodleQuickForm_group extends HTML_QuickForm_group{
    
    var $_helpbutton='';

    
    public function __construct($elementName=null, $elementLabel=null, $elements=null, $separator=null, $appendName = true) {
        parent::__construct($elementName, $elementLabel, $elements, $separator, $appendName);
    }

    
    public function MoodleQuickForm_group($elementName=null, $elementLabel=null, $elements=null, $separator=null, $appendName = true) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $elements, $separator, $appendName);
    }

    
    
    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            if ($this->getGroupType() == 'submit'){
                return 'nodisplay';
            } else {
                return 'static';
            }
        } else {
            if ($this->getGroupType() == 'submit') {
                return 'actionbuttons';
            }
            return 'fieldset';
        }
    }

    
    function setElements($elements){
        parent::setElements($elements);
        foreach ($this->_elements as $element){
            if (method_exists($element, 'setHiddenLabel')){
                $element->setHiddenLabel(true);
            }
        }
    }
}
