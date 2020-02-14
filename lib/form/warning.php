<?php



require_once("HTML/QuickForm/static.php");


class MoodleQuickForm_warning extends HTML_QuickForm_static{
    
    var $_elementTemplateType='warning';

    
    var $_helpbutton='';

    
    var $_class='';

    
    public function __construct($elementName=null, $elementClass='notifyproblem', $text=null) {
        parent::__construct($elementName, null, $text);
        $this->_type = 'warning';
        if (is_null($elementClass)) {
            $elementClass = 'notifyproblem';
        }
        $this->_class = $elementClass;
    }

    
    public function MoodleQuickForm_warning($elementName=null, $elementClass='notifyproblem', $text=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementClass, $text);
    }

    
    function toHtml() {
        global $OUTPUT;
        return $OUTPUT->notification($this->_text, $this->_class);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function getElementTemplateType(){
        return $this->_elementTemplateType;
    }
}
