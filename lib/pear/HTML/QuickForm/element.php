<?php


require_once('HTML/Common.php');


class HTML_QuickForm_element extends HTML_Common
{
    
    
    var $_label = '';

    
    var $_type = '';

    
    var $_flagFrozen = false;

    
    var $_persistantFreeze = false;
    
            
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($attributes);
        if (isset($elementName)) {
            $this->setName($elementName);
        }
        if (isset($elementLabel)) {
            $this->setLabel($elementLabel);
        }
    } 
    
    public function HTML_QuickForm_element($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }
    
        
    
    function apiVersion()
    {
        return 2.0;
    } 
        
    
    function getType()
    {
        return $this->_type;
    } 
        
    
    function setName($name)
    {
            }     
        
    
    function getName()
    {
            }     
        
    
    function setValue($value)
    {
            } 
        
    
    function getValue()
    {
                return null;
    }     
        
    
    function freeze()
    {
        $this->_flagFrozen = true;
    } 
        
   
    function unfreeze()
    {
        $this->_flagFrozen = false;
    }

        
    
    function getFrozenHtml()
    {
        $value = $this->getValue();
        return ('' != $value? htmlspecialchars($value): '&nbsp;') .
               $this->_getPersistantData();
    }     
        
   
    function _getPersistantData()
    {
        if (!$this->_persistantFreeze) {
            return '';
        } else {
            $id = $this->getAttribute('id');
            if (isset($id)) {
                                $id = array('id' => $id . '_persistant');
            } else {
                $id = array();
            }

            return '<input' . $this->_getAttrString(array(
                       'type'  => 'hidden',
                       'name'  => $this->getName(),
                       'value' => $this->getValue()
                   ) + $id) . ' />';
        }
    }

        
    
    function isFrozen()
    {
        return $this->_flagFrozen;
    } 
        
    
    function setPersistantFreeze($persistant=false)
    {
        $this->_persistantFreeze = $persistant;
    } 
        
    
    function setLabel($label)
    {
        $this->_label = $label;
    } 
        
    
    function getLabel()
    {
        return $this->_label;
    } 
        
    
    function _findValue(&$values)
    {
        if (empty($values)) {
            return null;
        }
        $elementName = $this->getName();
        if (isset($values[$elementName])) {
            return $values[$elementName];
        } elseif (strpos($elementName, '[')) {
            $myVar = "['" . str_replace(array(']', '['), array('', "']['"), $elementName) . "']";
            return eval("return (isset(\$values$myVar)) ? \$values$myVar : null;");
        } else {
            return null;
        }
    } 
        
    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                static::__construct($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]);
                break;
            case 'addElement':
                $this->onQuickFormEvent('createElement', $arg, $caller);
                $this->onQuickFormEvent('updateValue', null, $caller);
                break;
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
            case 'setGroupValue':
                $this->setValue($arg);
        }
        return true;
    } 
        
   
    function accept(&$renderer, $required=false, $error=null)
    {
        $renderer->renderElement($this, $required, $error);
    } 
        
   
    function _generateId() {
        if ($this->getAttribute('id')) {
            return;
        }

        $id = $this->getName();
        $id = 'id_' . str_replace(array('qf_', '[', ']'), array('', '_', ''), $id);
        $id = clean_param($id, PARAM_ALPHANUMEXT);
        $this->updateAttributes(array('id' => $id));
    }

        
   
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getValue();
        }
        return $this->_prepareValue($value, $assoc);
    }
    
        
   
    function _prepareValue($value, $assoc)
    {
        if (null === $value) {
            return null;
        } elseif (!$assoc) {
            return $value;
        } else {
            $name = $this->getName();
            if (!strpos($name, '[')) {
                return array($name => $value);
            } else {
                $valueAry = array();
                $myIndex  = "['" . str_replace(array(']', '['), array('', "']['"), $name) . "']";
                eval("\$valueAry$myIndex = \$value;");
                return $valueAry;
            }
        }
    }
    
    } ?>
