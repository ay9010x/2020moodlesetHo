<?php



require_once('select.php');


class MoodleQuickForm_searchableselector extends MoodleQuickForm_select{
    
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
                if (empty($attributes) || empty($attributes['size'])) {
            $attributes['size'] = 12;
        }
        parent::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    public function MoodleQuickForm_searchableselector($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    function toHtml(){
        global $OUTPUT;
        if ($this->_hiddenLabel || $this->_flagFrozen) {
            return parent::toHtml();
        } else {
                        global $PAGE;
            $PAGE->requires->js('/lib/form/searchableselector.js');
            $PAGE->requires->js_function_call('selector.filter_init', array(get_string('search'),$this->getAttribute('id')));

            $strHtml = '';
            $strHtml .= parent::toHtml();             return $strHtml;
        }
    }

}
