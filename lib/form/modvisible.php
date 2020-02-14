<?php




global $CFG;
require_once "$CFG->libdir/form/select.php";


class MoodleQuickForm_modvisible extends MoodleQuickForm_select{

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes, null);
        $this->_type = 'modvisible';
    }

    
    public function MoodleQuickForm_modvisible($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                $choices=array();
                $choices[1] = get_string('show');
                $choices[0] = get_string('hide');
                $this->load($choices);
                break;

        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }
}
