<?php




require_once('HTML/QuickForm/checkbox.php');


class MoodleQuickForm_checkbox extends HTML_QuickForm_checkbox{
    
    var $_helpbutton='';

    
    public function __construct($elementName=null, $elementLabel=null, $text='', $attributes=null) {
        parent::__construct($elementName, $elementLabel, $text, $attributes);
    }

    
    public function MoodleQuickForm_checkbox($elementName=null, $elementLabel=null, $text='', $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $text, $attributes);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
                        switch ($event) {
            case 'updateValue':
                                                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                                                            if ($caller->isSubmitted()) {
                        $value = $this->_findValue($caller->_submitValues);
                    } else {

                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                                $this->setChecked($value);
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    }

    
    function toHtml()
    {
        return '<span>' . parent::toHtml() . '</span>';
    }

    
    function getFrozenHtml()
    {
                $output = '<input type="checkbox" disabled="disabled" id="'.$this->getAttribute('id').'" ';
        if ($this->getChecked()) {
            $output .= 'checked="checked" />'.$this->_getPersistantData();
        } else {
            $output .= '/>';
        }
        return $output;
    }
}
