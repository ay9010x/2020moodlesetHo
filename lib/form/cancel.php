<?php




if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

global $CFG;
require_once($CFG->libdir.'/form/submit.php');


class MoodleQuickForm_cancel extends MoodleQuickForm_submit
{
    
    public function __construct($elementName=null, $value=null, $attributes=null)
    {
        if ($elementName==null){
            $elementName='cancel';
        }
        if ($value==null){
            $value=get_string('cancel');
        }
        parent::__construct($elementName, $value, $attributes);
        $this->updateAttributes(array('onclick'=>'skipClientValidation = true; return true;'));

                $class = $this->getAttribute('class');
        if (empty($class)) {
            $class = '';
        }
        $this->updateAttributes(array('class' => $class . ' btn-cancel'));
    }

    
    public function MoodleQuickForm_cancel($elementName=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $value, $attributes);
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                static::__construct($arg[0], $arg[1], $arg[2]);
                $caller->_registerCancelButton($this->getName());
                return true;
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }

    
    function getFrozenHtml(){
        return HTML_QuickForm_submit::getFrozenHtml();
    }

    
    function freeze(){
        return HTML_QuickForm_submit::freeze();
    }
}
