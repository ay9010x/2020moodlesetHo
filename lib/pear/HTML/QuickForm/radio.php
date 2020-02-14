<?php


require_once('HTML/QuickForm/input.php');


class HTML_QuickForm_radio extends HTML_QuickForm_input
{
    
    
    var $_text = '';

        
    
    public function __construct($elementName=null, $elementLabel=null, $text=null, $value=null, $attributes=null) {
                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes);
        if (isset($value)) {
            $this->setValue($value);
        }
        $this->_persistantFreeze = true;
        $this->setType('radio');
        $this->_text = $text;
    } 
    
    public function HTML_QuickForm_radio($elementName=null, $elementLabel=null, $text=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $text, $value, $attributes);
    }

    
    function _generateId() {
                        
        if ($this->getAttribute('id')) {
            return;
        }

        parent::_generateId();
        $id = $this->getAttribute('id') . '_' . clean_param($this->getValue(), PARAM_ALPHANUMEXT);
        $this->updateAttributes(array('id' => $id));
    }

    
    
    function setChecked($checked)
    {
        if (!$checked) {
            $this->removeAttribute('checked');
        } else {
            $this->updateAttributes(array('checked'=>'checked'));
        }
    } 
        
    
    function getChecked()
    {
        return $this->getAttribute('checked');
    }         
        
    
    function toHtml()
    {
        if (0 == strlen($this->_text)) {
            $label = '';
        } elseif ($this->_flagFrozen) {
            $label = $this->_text;
        } else {
            $label = '<label for="' . $this->getAttribute('id') . '">' . $this->_text . '</label>';
        }
        return HTML_QuickForm_input::toHtml() . $label;
    }     
        
    
    function getFrozenHtml()
    {
        if ($this->getChecked()) {
            return '<tt>(x)</tt>' .
                   $this->_getPersistantData();
        } else {
            return '<tt>( )</tt>';
        }
    } 
        
    
    function setText($text)
    {
        $this->_text = $text;
    } 
        
    
    function getText()
    {
        return $this->_text;
    } 
        
    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                                                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if ($value == $this->getValue()) {
                    $this->setChecked(true);
                } else {
                    $this->setChecked(false);
                }
                break;
            case 'setGroupValue':
                if ($arg == $this->getValue()) {
                    $this->setChecked(true);
                } else {
                    $this->setChecked(false);
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } 
        
   
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getChecked()? $this->getValue(): null;
        } elseif ($value != $this->getValue()) {
            $value = null;
        }
        return $this->_prepareValue($value, $assoc);
    }
    
    } ?>
