<?php




require_once("HTML/QuickForm/submit.php");


class MoodleQuickForm_submit extends HTML_QuickForm_submit {
    
    public function __construct($elementName=null, $value=null, $attributes=null) {
        parent::__construct($elementName, $value, $attributes);
    }

    
    public function MoodleQuickForm_submit($elementName=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $value, $attributes);
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                parent::onQuickFormEvent($event, $arg, $caller);
                if ($caller->isNoSubmitButton($arg[0])){
                                                                                $onClick = $this->getAttribute('onclick');
                    $skip = 'skipClientValidation = true;';
                    $onClick = ($onClick !== null)?$skip.' '.$onClick:$skip;
                    $this->updateAttributes(array('onclick'=>$onClick));
                }
                return true;
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);

    }

    
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'nodisplay';
        } else {
            return 'actionbuttons';
        }
    }

    
    function freeze(){
        $this->_flagFrozen = true;
    }

}
