<?php




global $CFG;
require_once "$CFG->libdir/form/select.php";


class MoodleQuickForm_selectyesno extends MoodleQuickForm_select{
    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes, null);
        $this->_type = 'selectyesno';
        $this->_persistantFreeze = true;
    }

    
    public function MoodleQuickForm_selectyesno($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                $choices=array();
                $choices[0] = get_string('no');
                $choices[1] = get_string('yes');
                $this->load($choices);
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }

}
