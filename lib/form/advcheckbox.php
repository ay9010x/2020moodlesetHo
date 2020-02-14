<?php




require_once('HTML/QuickForm/advcheckbox.php');


class MoodleQuickForm_advcheckbox extends HTML_QuickForm_advcheckbox{
    
    var $_helpbutton='';

    
    var $_group;

    
    public function __construct($elementName=null, $elementLabel=null, $text=null, $attributes=null, $values=null)
    {
        if ($values === null){
            $values = array(0, 1);
        }

        if (!empty($attributes['group'])) {

            $this->_group = 'checkboxgroup' . $attributes['group'];
            unset($attributes['group']);
            if (is_null($attributes)) {
                $attributes = array();
                $attributes['class'] .= " $this->_group";
            } elseif (is_array($attributes)) {
                if (isset($attributes['class'])) {
                    $attributes['class'] .= " $this->_group";
                } else {
                    $attributes['class'] = $this->_group;
                }
            } elseif ($strpos = stripos($attributes, 'class="')) {
                $attributes = str_ireplace('class="', 'class="' . $this->_group . ' ', $attributes);
            } else {
                $attributes .= ' class="' . $this->_group . '"';
            }
        }

        parent::__construct($elementName, $elementLabel, $text, $attributes, $values);
    }

    
    public function MoodleQuickForm_advcheckbox($elementName=null, $elementLabel=null, $text=null, $attributes=null, $values=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $text, $attributes, $values);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
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
