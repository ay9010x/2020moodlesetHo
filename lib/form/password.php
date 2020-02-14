<?php




require_once('HTML/QuickForm/password.php');


class MoodleQuickForm_password extends HTML_QuickForm_password{
    
    var $_helpbutton='';

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        global $CFG;
                if (empty($attributes)) {
            $attributes = array('autocomplete'=>'off');
        } else if (is_array($attributes)) {
            $attributes['autocomplete'] = 'off';
        } else {
            if (strpos($attributes, 'autocomplete') === false) {
                $attributes .= ' autocomplete="off" ';
            }
        }

        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_password($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }
}
