<?php


require_once("HTML/QuickForm/element.php");


class HTML_QuickForm_textarea extends HTML_QuickForm_element
{
    
    
    var $_value = null;

                
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'textarea';
    } 
    
    public function HTML_QuickForm_textarea($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
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
        $this->_value = $value;
    }     
        
    
    function getValue()
    {
        return $this->_value;
    } 
        
    
    function setWrap($wrap)
    {
        $this->updateAttributes(array('wrap' => $wrap));
    }     
        
    
    function setRows($rows)
    {
        $this->updateAttributes(array('rows' => $rows));
    } 
        
     
    function setCols($cols)
    {
        $this->updateAttributes(array('cols' => $cols));
    } 
        
    
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            return $this->_getTabs() .
                   '<textarea' . $this->_getAttrString($this->_attributes) . '>' .
                                      preg_replace("/(\r\n|\n|\r)/", '&#010;', htmlspecialchars($this->_value)) .
                   '</textarea>';
        }
    }     
        
    
    function getFrozenHtml()
    {
        $value = htmlspecialchars($this->getValue());
        if ($this->getAttribute('wrap') == 'off') {
            $html = $this->_getTabs() . '<pre>' . $value."</pre>\n";
        } else {
            $html = nl2br($value)."\n";
        }
        return $html . $this->_getPersistantData();
    } 
    
} ?>
