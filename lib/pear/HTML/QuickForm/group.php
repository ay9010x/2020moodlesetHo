<?php


require_once("HTML/QuickForm/element.php");


class HTML_QuickForm_group extends HTML_QuickForm_element
{
    
    
    var $_name = '';

    
    var $_elements = array();

    
    var $_separator = null;

    
    var $_required = array();

   
    var $_appendName = true;

        
    
    public function __construct($elementName=null, $elementLabel=null, $elements=null, $separator=null, $appendName = true) {
        parent::__construct($elementName, $elementLabel);
        $this->_type = 'group';
        if (isset($elements) && is_array($elements)) {
            $this->setElements($elements);
        }
        if (isset($separator)) {
            $this->_separator = $separator;
        }
        if (isset($appendName)) {
            $this->_appendName = $appendName;
        }
    } 
    
    public function HTML_QuickForm_group($elementName=null, $elementLabel=null, $elements=null, $separator=null, $appendName = true) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $elements, $separator, $appendName);
    }

        
    
    function setName($name)
    {
        $this->_name = $name;
    } 
        
    
    function getName()
    {
        return $this->_name;
    } 
        
    
    function setValue($value)
    {
        $this->_createElementsIfNotExist();
        foreach (array_keys($this->_elements) as $key) {
            if (!$this->_appendName) {
                $v = $this->_elements[$key]->_findValue($value);
                if (null !== $v) {
                    $this->_elements[$key]->onQuickFormEvent('setGroupValue', $v, $this);
                }

            } else {
                $elementName = $this->_elements[$key]->getName();
                $index       = strlen($elementName) ? $elementName : $key;
                if (is_array($value)) {
                    if (isset($value[$index])) {
                        $this->_elements[$key]->onQuickFormEvent('setGroupValue', $value[$index], $this);
                    }
                } elseif (isset($value)) {
                    $this->_elements[$key]->onQuickFormEvent('setGroupValue', $value, $this);
                }
            }
        }
    } 
        
    
    function getValue()
    {
        $value = null;
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            switch ($element->getType()) {
                case 'radio':
                    $v = $element->getChecked()? $element->getValue(): null;
                    break;
                case 'checkbox':
                    $v = $element->getChecked()? true: null;
                    break;
                default:
                    $v = $element->getValue();
            }
            if (null !== $v) {
                $elementName = $element->getName();
                if (is_null($elementName)) {
                    $value = $v;
                } else {
                    if (!is_array($value)) {
                        $value = is_null($value)? array(): array($value);
                    }
                    if ('' === $elementName) {
                        $value[] = $v;
                    } else {
                        $value[$elementName] = $v;
                    }
                }
            }
        }
        return $value;
    } 
        
    
    function setElements($elements)
    {
        $this->_elements = array_values($elements);
        if ($this->_flagFrozen) {
            $this->freeze();
        }
    } 
        
    
    function &getElements()
    {
        $this->_createElementsIfNotExist();
        return $this->_elements;
    } 
        
    
    function getGroupType()
    {
        $this->_createElementsIfNotExist();
        $prevType = '';
        foreach (array_keys($this->_elements) as $key) {
            $type = $this->_elements[$key]->getType();
            if ($type != $prevType && $prevType != '') {
                return 'mixed';
            }
            $prevType = $type;
        }
        return $type;
    } 
        
    
    function toHtml()
    {
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        $this->accept($renderer);
        return $renderer->toHtml();
    } 
        
    
    function getElementName($index)
    {
        $this->_createElementsIfNotExist();
        $elementName = false;
        if (is_int($index) && isset($this->_elements[$index])) {
            $elementName = $this->_elements[$index]->getName();
            if (isset($elementName) && $elementName == '') {
                $elementName = $index;
            }
            if ($this->_appendName) {
                if (is_null($elementName)) {
                    $elementName = $this->getName();
                } else {
                    $elementName = $this->getName().'['.$elementName.']';
                }
            }

        } elseif (is_string($index)) {
            foreach (array_keys($this->_elements) as $key) {
                $elementName = $this->_elements[$key]->getName();
                if ($index == $elementName) {
                    if ($this->_appendName) {
                        $elementName = $this->getName().'['.$elementName.']';
                    }
                    break;
                } elseif ($this->_appendName && $this->getName().'['.$elementName.']' == $index) {
                    break;
                }
            }
        }
        return $elementName;
    } 
        
    
    function getFrozenHtml()
    {
        $flags = array();
        $this->_createElementsIfNotExist();
        foreach (array_keys($this->_elements) as $key) {
            if (false === ($flags[$key] = $this->_elements[$key]->isFrozen())) {
                $this->_elements[$key]->freeze();
            }
        }
        $html = $this->toHtml();
        foreach (array_keys($this->_elements) as $key) {
            if (!$flags[$key]) {
                $this->_elements[$key]->unfreeze();
            }
        }
        return $html;
    } 
        
    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                $this->_createElementsIfNotExist();
                foreach (array_keys($this->_elements) as $key) {
                    if ($this->_appendName) {
                        $elementName = $this->_elements[$key]->getName();
                        if (is_null($elementName)) {
                            $this->_elements[$key]->setName($this->getName());
                        } elseif ('' === $elementName) {
                            $this->_elements[$key]->setName($this->getName() . '[' . $key . ']');
                        } else {
                            $this->_elements[$key]->setName($this->getName() . '[' . $elementName . ']');
                        }
                    }
                    $this->_elements[$key]->onQuickFormEvent('updateValue', $arg, $caller);
                    if ($this->_appendName) {
                        $this->_elements[$key]->setName($elementName);
                    }
                }
                break;

            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } 
        
   
    function accept(&$renderer, $required = false, $error = null)
    {
        $this->_createElementsIfNotExist();
        $renderer->startGroup($this, $required, $error);
        $name = $this->getName();
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];

            if ($this->_appendName) {
                $elementName = $element->getName();
                if (isset($elementName)) {
                    $element->setName($name . '['. (strlen($elementName)? $elementName: $key) .']');
                } else {
                    $element->setName($name);
                }
            }

            $required = !$element->isFrozen() && in_array($element->getName(), $this->_required);

            $element->accept($renderer, $required);

                        if ($this->_appendName) {
                $element->setName($elementName);
            }
        }
        $renderer->finishGroup($this);
    } 
        
   
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = null;
        foreach (array_keys($this->_elements) as $key) {
            $elementName = $this->_elements[$key]->getName();
            if ($this->_appendName) {
                if (is_null($elementName)) {
                    $this->_elements[$key]->setName($this->getName());
                } elseif ('' === $elementName) {
                    $this->_elements[$key]->setName($this->getName() . '[' . $key . ']');
                } else {
                    $this->_elements[$key]->setName($this->getName() . '[' . $elementName . ']');
                }
            }
            $v = $this->_elements[$key]->exportValue($submitValues, $assoc);
            if ($this->_appendName) {
                $this->_elements[$key]->setName($elementName);
            }
            if (null !== $v) {
                                if (null === $value) {
                    $value = array();
                }
                if ($assoc) {
                                        $value = HTML_QuickForm::arrayMerge($value, $v);
                } else {
                                        if (is_null($elementName)) {
                        $value = $v;
                    } elseif ('' === $elementName) {
                        $value[] = $v;
                    } else {
                        $value[$elementName] = $v;
                    }
                }
            }
        }
                return $value;
    }

        
   
    function _createElements()
    {
            }

        
   
    function _createElementsIfNotExist()
    {
        if (empty($this->_elements)) {
            $this->_createElements();
            if ($this->_flagFrozen) {
                $this->freeze();
            }
        }
    }

        
    function freeze()
    {
        parent::freeze();
        foreach (array_keys($this->_elements) as $key) {
            $this->_elements[$key]->freeze();
        }
    }

        
    function unfreeze()
    {
        parent::unfreeze();
        foreach (array_keys($this->_elements) as $key) {
            $this->_elements[$key]->unfreeze();
        }
    }

        
    function setPersistantFreeze($persistant = false)
    {
        parent::setPersistantFreeze($persistant);
        foreach (array_keys($this->_elements) as $key) {
            $this->_elements[$key]->setPersistantFreeze($persistant);
        }
    }

    } ?>