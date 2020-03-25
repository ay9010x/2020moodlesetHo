<?php


require_once("HTML/QuickForm/element.php");


class HTML_QuickForm_input extends HTML_QuickForm_element
{
    
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
    } 
    
    public function HTML_QuickForm_input($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

        
    
    function setType($type)
    {
        $this->_type = $type;
        $this->updateAttributes(array('type'=>$type));
    }     
        
    
    function setName($name)
    {
        $this->updateAttributes(array('name'=>$name));
    }     
        
    
    function getName()
    {
        return $this->getAttribute('name');
    }     
        
    
    function setValue($value)
    {
        $this->updateAttributes(array('value'=>$value));
    } 
        
    
    function getValue()
    {
        return $this->getAttribute('value');
    }     
        
    
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            return $this->_getTabs() . '<input' . $this->_getAttrString($this->_attributes) . ' />';
        }
    } 
        
    
    function onQuickFormEvent($event, $arg, &$caller)
    {
                $type = $this->getType();
        if (('updateValue' != $event) ||
            ('submit' != $type && 'reset' != $type && 'image' != $type && 'button' != $type)) {
            parent::onQuickFormEvent($event, $arg, $caller);
        } else {
            $value = $this->_findValue($caller->_constantValues);
            if (null === $value) {
                $value = $this->_findValue($caller->_defaultValues);
            }
            if (null !== $value) {
                $this->setValue($value);
            }
        }
        return true;
    } 
        
   
    function exportValue(&$submitValues, $assoc = false)
    {
        $type = $this->getType();
        if ('reset' == $type || 'image' == $type || 'button' == $type || 'file' == $type) {
            return null;
        } else {
            return parent::exportValue($submitValues, $assoc);
        }
    }
    
    } ?>
