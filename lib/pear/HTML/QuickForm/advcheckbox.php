<?php


require_once('HTML/QuickForm/checkbox.php');


class HTML_QuickForm_advcheckbox extends HTML_QuickForm_checkbox
{
    
    
    var $_values = null;

    
    var $_currentValue = null;

        
    
    public function __construct($elementName=null, $elementLabel=null, $text=null, $attributes=null, $values=null) {
        parent::__construct($elementName, $elementLabel, $text, $attributes);
        $this->setValues($values);
    } 
    
    public function HTML_QuickForm_advcheckbox($elementName=null, $elementLabel=null, $text=null, $attributes=null, $values=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $text, $attributes, $values);
    }

        
    
    function getPrivateName($elementName)
    {
        return '__'.$elementName;
    }

        
    
    function getOnclickJs($elementName)
    {
        $onclickJs = 'if (this.checked) { this.form[\''.$elementName.'\'].value=\''.addcslashes($this->_values[1], '\'').'\'; }';
        $onclickJs .= 'else { this.form[\''.$elementName.'\'].value=\''.addcslashes($this->_values[0], '\'').'\'; }';
        return $onclickJs;
    }

        
    
    function setValues($values)
    {
        if (empty($values)) {
                        $this->_values = array('', 1);
        } elseif (is_scalar($values)) {
                                    $this->_values = array('', $values);
        } else {
            $this->_values = $values;
        }
        $this->updateAttributes(array('value' => $this->_values[1]));
        $this->setChecked($this->_currentValue == $this->_values[1]);
    }

        
   
    function setValue($value)
    {
        $this->setChecked(isset($this->_values[1]) && $value == $this->_values[1]);
        $this->_currentValue = $value;
    }

        
   
    function getValue()
    {
        if (is_array($this->_values)) {
            return $this->_values[$this->getChecked()? 1: 0];
        } else {
            return null;
        }
    }

        
    
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return parent::toHtml();
        } else {
            return '<input' . $this->_getAttrString(array(
                        'type'  => 'hidden',
                        'name'  => $this->getName(),
                        'value' => $this->_values[0]
                   )) . ' />' . parent::toHtml();

        }
    } 
        
   
    function getFrozenHtml()
    {
        return ($this->getChecked()? '<tt>[x]</tt>': '<tt>[ ]</tt>') .
               $this->_getPersistantData();
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
                if (null !== $value) {
                    $this->setValue($value);
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
            $value = $this->getValue();
        } elseif (is_array($this->_values) && ($value != $this->_values[0]) && ($value != $this->_values[1])) {
            $value = null;
        }
        return $this->_prepareValue($value, $assoc);
    }
    } ?>
