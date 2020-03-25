<?php




require_once('HTML/QuickForm/select.php');


class MoodleQuickForm_selectwithlink extends HTML_QuickForm_select{
    
    var $_helpbutton='';

    
    var $_hiddenLabel=false;

    
    var $_link=null;

    
    var $_linklabel=null;

    
    var $_linkreturn=null;

    
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null, $linkdata=null) {
        if (!empty($linkdata['link']) && !empty($linkdata['label'])) {
            $this->_link = $linkdata['link'];
            $this->_linklabel = $linkdata['label'];
        }

        if (!empty($linkdata['return'])) {
            $this->_linkreturn = $linkdata['return'];
        }

        parent::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    public function MoodleQuickForm_selectwithlink($elementName=null, $elementLabel=null, $options=null, $attributes=null, $linkdata=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes, $linkdata);
    }

    
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }

    
    function toHtml(){
        $retval = '';
        if ($this->_hiddenLabel){
            $this->_generateId();
            $retval = '<label class="accesshide" for="'.$this->getAttribute('id').'" >'.
                        $this->getLabel().'</label>'.parent::toHtml();
        } else {
             $retval = parent::toHtml();
        }

        if (!empty($this->_link)) {
            if (!empty($this->_linkreturn) && is_array($this->_linkreturn)) {
                $appendchar = '?';
                if (strstr($this->_link, '?')) {
                    $appendchar = '&amp;';
                }

                foreach ($this->_linkreturn as $key => $val) {
                    $this->_link .= $appendchar."$key=$val";
                    $appendchar = '&amp;';
                }
            }

            $retval .= '<a style="margin-left: 5px" href="'.$this->_link.'">'.$this->_linklabel.'</a>';
        }

        return $retval;
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
