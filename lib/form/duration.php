<?php




global $CFG;
require_once($CFG->libdir . '/form/group.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/text.php');


class MoodleQuickForm_duration extends MoodleQuickForm_group {
   
   protected $_options = array('optional' => false, 'defaultunit' => 60);

   
   private $_units = null;

   
    public function __construct($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'duration';

                if (!is_array($options)) {
            $options = array();
        }
        $this->_options['optional'] = !empty($options['optional']);
        if (isset($options['defaultunit'])) {
            if (!array_key_exists($options['defaultunit'], $this->get_units())) {
                throw new coding_exception($options['defaultunit'] .
                        ' is not a recognised unit in MoodleQuickForm_duration.');
            }
            $this->_options['defaultunit'] = $options['defaultunit'];
        }
    }

    
    public function MoodleQuickForm_duration($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    public function get_units() {
        if (is_null($this->_units)) {
            $this->_units = array(
                604800 => get_string('weeks'),
                86400 => get_string('days'),
                3600 => get_string('hours'),
                60 => get_string('minutes'),
                1 => get_string('seconds'),
            );
        }
        return $this->_units;
    }

    
    public function seconds_to_unit($seconds) {
        if ($seconds == 0) {
            return array(0, $this->_options['defaultunit']);
        }
        foreach ($this->get_units() as $unit => $notused) {
            if (fmod($seconds, $unit) == 0) {
                return array($seconds / $unit, $unit);
            }
        }
        return array($seconds, 1);
    }

    
    function _createElements() {
        $attributes = $this->getAttributes();
        if (is_null($attributes)) {
            $attributes = array();
        }
        if (!isset($attributes['size'])) {
            $attributes['size'] = 3;
        }
        $this->_elements = array();
                $this->_elements[] = @MoodleQuickForm::createElement('text', 'number', get_string('time', 'form'), $attributes, true);
        unset($attributes['size']);
        $this->_elements[] = @MoodleQuickForm::createElement('select', 'timeunit', get_string('timeunit', 'form'), $this->get_units(), $attributes, true);
                if($this->_options['optional']) {
            $this->_elements[] = @MoodleQuickForm::createElement('checkbox', 'enabled', null, get_string('enable'), $this->getAttributes(), true);
        }
        foreach ($this->_elements as $element){
            if (method_exists($element, 'setHiddenLabel')){
                $element->setHiddenLabel(true);
            }
        }
    }

    
    function onQuickFormEvent($event, $arg, &$caller) {
        switch ($event) {
            case 'updateValue':
                                                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                                                            if ($caller->isSubmitted()) {
                        $value = $this->_findValue($caller->_submitValues);
                    } else {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (!is_array($value)) {
                    list($number, $unit) = $this->seconds_to_unit($value);
                    $value = array('number' => $number, 'timeunit' => $unit);
                                        if ($this->_options['optional']) {
                        $value['enabled'] = $number != 0;
                    }
                } else {
                    $value['enabled'] = isset($value['enabled']);
                }
                if (null !== $value){
                    $this->setValue($value);
                }
                break;

            case 'createElement':
                if ($arg[2]['optional']) {
                    $caller->disabledIf($arg[0], $arg[0] . '[enabled]');
                }
                $caller->setType($arg[0] . '[number]', PARAM_FLOAT);
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;

            default:
                return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    
    function toHtml() {
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);
        return $renderer->toHtml();
    }

    
    function accept(&$renderer, $required = false, $error = null) {
        $renderer->renderElement($this, $required, $error);
    }

    
    function exportValue(&$submitValues, $notused = false) {
                $valuearray = array();
        foreach ($this->_elements as $element) {
            $thisexport = $element->exportValue($submitValues[$this->getName()], true);
            if (!is_null($thisexport)) {
                $valuearray += $thisexport;
            }
        }

                if (empty($valuearray)) {
            return null;
        }
        if ($this->_options['optional'] && empty($valuearray['enabled'])) {
            return array($this->getName() => 0);
        }
        return array($this->getName() => $valuearray['number'] * $valuearray['timeunit']);
    }
}
