<?php




require_once('HTML/QuickForm/textarea.php');


class MoodleQuickForm_textarea extends HTML_QuickForm_textarea{
    
    var $_formid = '';

    
    var $_helpbutton='';

    
    var $_hiddenLabel=false;

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_textarea($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }

    
    function toHtml(){
        if ($this->_hiddenLabel){
            $this->_generateId();
            return '<label class="accesshide" for="' . $this->getAttribute('id') . '" >' .
                    $this->getLabel() . '</label>' . parent::toHtml();
        } else {
            return parent::toHtml();
        }
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                $this->_formid = $caller->getAttribute('id');
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }

    
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }
}
