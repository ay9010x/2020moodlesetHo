<?php


require_once('HTML/QuickForm/select.php');


class HTML_QuickForm_hiddenselect extends HTML_QuickForm_select
{
    
    
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'hiddenselect';
        if (isset($options)) {
            $this->load($options);
        }
    } 
    
    public function HTML_QuickForm_hiddenselect($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

        
    
    function toHtml()
    {
        $tabs    = $this->_getTabs();
        $name    = $this->getPrivateName();
        $strHtml = '';

        foreach ($this->_values as $key => $val) {
            for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++) {
                if ($val == $this->_options[$i]['attr']['value']) {
                    $strHtml .= $tabs . '<input' . $this->_getAttrString(array(
                        'type'  => 'hidden',
                        'name'  => $name,
                        'value' => $val
                    )) . " />\n" ;
                }
            }
        }

        return $strHtml;
    } 
        
   
    function accept(&$renderer, $required=false, $error=null)
    {
        $renderer->renderHidden($this);
    }

    } ?>
