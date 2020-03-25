<?php




require_once("HTML/QuickForm/text.php");


class MoodleQuickForm_text extends HTML_QuickForm_text{
    
    var $_helpbutton='';

    
    var $_hiddenLabel=false;

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_text($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }

    
    function freeze()
    {
        $this->_flagFrozen = true;
                $this->setPersistantFreeze(false);
    } 
    
    function getFrozenHtml()
    {
        $attributes = array('readonly' => 'readonly');
        $this->updateAttributes($attributes);
        return $this->_getTabs() . '<input' . $this->_getAttrString($this->_attributes) . ' />' . $this->_getPersistantData();
    } 
    
    function toHtml(){
        if ($this->_hiddenLabel){
            $this->_generateId();
            return '<label class="accesshide" for="'.$this->getAttribute('id').'" >'.
                        $this->getLabel().'</label>'.parent::toHtml();
        } else {
             return parent::toHtml();
        }
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }
}
