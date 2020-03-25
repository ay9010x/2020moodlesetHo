<?php




global $CFG;
require_once("HTML/QuickForm/element.php");
require_once($CFG->dirroot.'/grade/grading/form/lib.php');

if (class_exists('HTML_QuickForm')) {
    HTML_QuickForm::registerRule('gradingvalidated', 'callback', '_validate', 'MoodleQuickForm_grading');
}


class MoodleQuickForm_grading extends HTML_QuickForm_input{
    
    var $_helpbutton='';

    
    private $gradingattributes;

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->gradingattributes = $attributes;
    }

    
    public function MoodleQuickForm_grading($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function get_gradinginstance() {
        if (is_array($this->gradingattributes) && array_key_exists('gradinginstance', $this->gradingattributes)) {
            return $this->gradingattributes['gradinginstance'];
        } else {
            return null;
        }
    }

    
    public function toHtml(){
        global $PAGE;
        return $this->get_gradinginstance()->render_grading_element($PAGE, $this);
    }

    
    public function getHelpButton(){
        return $this->_helpbutton;
    }

    
    public function getElementTemplateType(){
        return 'default';
    }

    
    public function onQuickFormEvent($event, $arg, &$caller) {
        if ($event == 'createElement') {
            $attributes = $arg[2];
            if (!is_array($attributes) || !array_key_exists('gradinginstance', $attributes) || !($attributes['gradinginstance'] instanceof gradingform_instance)) {
                throw new moodle_exception('exc_gradingformelement', 'grading');
            }
        }

        $name = $this->getName();
        if ($name && $caller->elementExists($name)) {
            $caller->addRule($name, $this->get_gradinginstance()->default_validation_error_message(), 'gradingvalidated', $this->gradingattributes);
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }

    
    public static function _validate($elementvalue, $attributes = null) {
        if (!$attributes['gradinginstance']->is_empty_form($elementvalue)) {
            return $attributes['gradinginstance']->validate_grading_element($elementvalue);
        }
        return true;
    }
}
