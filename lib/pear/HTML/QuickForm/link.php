<?php


require_once 'HTML/QuickForm/static.php';


class HTML_QuickForm_link extends HTML_QuickForm_static
{
    
    
    var $_text = "";

            
    
    public function __construct($elementName=null, $elementLabel=null, $href=null, $text=null, $attributes=null) {
                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = false;
        $this->_type = 'link';
        $this->setHref($href);
        $this->_text = $text;
    } 
    
    public function HTML_QuickForm_link($elementName=null, $elementLabel=null, $href=null, $text=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $href, $text, $attributes);
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
        return;
    }     
        
    
    function getValue()
    {
        return;
    } 
    
        
    
    function setHref($href)
    {
        $this->updateAttributes(array('href'=>$href));
    } 
        
    
    function toHtml()
    {
        $tabs = $this->_getTabs();
        $html = "$tabs<a".$this->_getAttrString($this->_attributes).">";
        $html .= $this->_text;
        $html .= "</a>";
        return $html;
    }     
        
    
    function getFrozenHtml()
    {
        return;
    } 
    
} ?>
