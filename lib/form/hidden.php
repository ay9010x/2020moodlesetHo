<?php




require_once('HTML/QuickForm/hidden.php');


class MoodleQuickForm_hidden extends HTML_QuickForm_hidden{
    
    var $_helpbutton='';

    
    public function __construct($elementName=null, $value='', $attributes=null) {
        parent::__construct($elementName, $value, $attributes);
    }

    
    public function MoodleQuickForm_hidden($elementName=null, $value='', $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        return self::__construct($elementName, $value, $attributes);
    }

    
    function setHelpButton($helpbuttonargs, $function='helpbutton'){
        throw new coding_exception('setHelpButton() can not be used any more, please see MoodleQuickForm::addHelpButton().');
    }

    
    function getHelpButton(){
        return '';
    }
}
