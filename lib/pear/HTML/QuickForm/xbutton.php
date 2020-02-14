<?php


require_once 'HTML/QuickForm/element.php';


class HTML_QuickForm_xbutton extends HTML_QuickForm_element
{
   
    var $_content; 

   
    public function __construct($elementName = null, $elementContent = null, $attributes = null) {
        parent::__construct($elementName, null, $attributes);
        $this->setContent($elementContent);
        $this->setPersistantFreeze(false);
        $this->_type = 'xbutton';
    }

    
    public function HTML_QuickForm_xbutton($elementName = null, $elementContent = null, $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementContent, $attributes);
    }

    function toHtml()
    {
        return '<button' . $this->getAttributes(true) . '>' . $this->_content . '</button>';
    }


    function getFrozenHtml()
    {
        return $this->toHtml();
    }


    function freeze()
    {
        return false;
    }


    function setName($name)
    {
        $this->updateAttributes(array(
            'name' => $name 
        ));
    }


    function getName()
    {
        return $this->getAttribute('name');
    }


    function setValue($value)
    {
        $this->updateAttributes(array(
            'value' => $value
        ));
    }


    function getValue()
    {
        return $this->getAttribute('value');
    }


   
    function setContent($content)
    {
        $this->_content = $content;
    }


    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' != $event) {
            return parent::onQuickFormEvent($event, $arg, $caller);
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
        if ('submit' == $this->getAttribute('type')) {
            return $this->_prepareValue($this->_findValue($submitValues), $assoc);
        } else {
            return null;
        }
    }
}
?>
