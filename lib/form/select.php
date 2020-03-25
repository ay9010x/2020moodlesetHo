<?php




require_once('HTML/QuickForm/select.php');


class MoodleQuickForm_select extends HTML_QuickForm_select{
    
    var $_helpbutton='';

    
    var $_hiddenLabel=false;

    
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    public function MoodleQuickForm_select($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }

    
    function toHtml(){
        $html = '';
        if ($this->getMultiple()) {
                                                $html .= '<input type="hidden" name="'.$this->getName().'" value="_qf__force_multiselect_submission">';
        }
        if ($this->_hiddenLabel){
            $this->_generateId();
            $html .= '<label class="accesshide" for="'.$this->getAttribute('id').'" >'.$this->getLabel().'</label>';
        }
        $html .= parent::toHtml();
        return $html;
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function removeOption($value)
    {
        $key=array_search($value, $this->_values);
        if ($key!==FALSE and $key!==null) {
            unset($this->_values[$key]);
        }
        foreach ($this->_options as $key=>$option){
            if ($option['attr']['value']==$value){
                unset($this->_options[$key]);
                                $this->_options = array_merge($this->_options);
                return;
            }
        }
    }

    
    function removeOptions()
    {
        $this->_options = array();
    }

    
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }

   
    function exportValue(&$submitValues, $assoc = false)
    {
        if (empty($this->_options)) {
            return $this->_prepareValue(null, $assoc);
        }

        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        }
        $value = (array)$value;

        $cleaned = array();
        foreach ($value as $v) {
            foreach ($this->_options as $option) {
                if ((string)$option['attr']['value'] === (string)$v) {
                    $cleaned[] = (string)$option['attr']['value'];
                    break;
                }
            }
        }

        if (empty($cleaned)) {
            return $this->_prepareValue(null, $assoc);
        }
        if ($this->getMultiple()) {
            return $this->_prepareValue($cleaned, $assoc);
        } else {
            return $this->_prepareValue($cleaned[0], $assoc);
        }
    }
}
