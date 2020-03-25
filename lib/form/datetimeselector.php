<?php




global $CFG;
require_once($CFG->libdir . '/form/group.php');
require_once($CFG->libdir . '/formslib.php');


class MoodleQuickForm_date_time_selector extends MoodleQuickForm_group {

    
    protected $_options = array();

    
    protected $_wrap = array('', '');

    
    protected $_usedcreateelement = true;

    
    public function __construct($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
                $calendartype = \core_calendar\type_factory::get_calendar_instance();
        $this->_options = array('startyear' => $calendartype->get_min_year(), 'stopyear' => $calendartype->get_max_year(),
            'defaulttime' => 0, 'timezone' => 99, 'step' => 5, 'optional' => false);

                HTML_QuickForm_element::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'date_time_selector';
                if (is_array($options)) {
            foreach ($options as $name => $value) {
                if (isset($this->_options[$name])) {
                    if (is_array($value) && is_array($this->_options[$name])) {
                        $this->_options[$name] = @array_merge($this->_options[$name], $value);
                    } else {
                        $this->_options[$name] = $value;
                    }
                }
            }
        }

                if ($calendartype->get_name() === 'gregorian') {
            form_init_date_js();
        }
    }

    
    public function MoodleQuickForm_date_time_selector($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    function _createElements() {
        global $OUTPUT;

                $calendartype = \core_calendar\type_factory::get_calendar_instance();

        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i += $this->_options['step']) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $this->_elements = array();
        $dateformat = $calendartype->get_date_order($this->_options['startyear'], $this->_options['stopyear']);
        if (right_to_left()) {               $this->_elements[] = @MoodleQuickForm::createElement('select', 'minute', get_string('minute', 'form'),
                $minutes, $this->getAttributes(), true);
            $this->_elements[] = @MoodleQuickForm::createElement('select', 'hour', get_string('hour', 'form'),
                $hours, $this->getAttributes(), true);
                        $dateformat = array_reverse($dateformat);
        }
        foreach ($dateformat as $key => $date) {
                        $this->_elements[] = @MoodleQuickForm::createElement('select', $key, get_string($key, 'form'), $date, $this->getAttributes(), true);
        }
        if (!right_to_left()) {               $this->_elements[] = @MoodleQuickForm::createElement('select', 'hour', get_string('hour', 'form'), $hours,
                $this->getAttributes(), true);
            $this->_elements[] = @MoodleQuickForm::createElement('select', 'minute', get_string('minute', 'form'), $minutes,
                $this->getAttributes(), true);
        }
                if ($calendartype->get_name() === 'gregorian') {
            $image = $OUTPUT->pix_icon('i/calendar', get_string('calendar', 'calendar'), 'moodle');
            $this->_elements[] = @MoodleQuickForm::createElement('link', 'calendar',
                    null, '#', $image,
                    array('class' => 'visibleifjs'));
        }
                if ($this->_options['optional']) {
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
                $requestvalue=$value;
                if ($value == 0) {
                    $value = $this->_options['defaulttime'];
                    if (!$value) {
                        $value = time();
                    }
                }
                if (!is_array($value)) {
                    $calendartype = \core_calendar\type_factory::get_calendar_instance();
                    $currentdate = $calendartype->timestamp_to_date_array($value, $this->_options['timezone']);
                                        $currentdate['minutes'] -= $currentdate['minutes'] % $this->_options['step'];
                    $value = array(
                        'minute' => $currentdate['minutes'],
                        'hour' => $currentdate['hours'],
                        'day' => $currentdate['mday'],
                        'month' => $currentdate['mon'],
                        'year' => $currentdate['year']);
                                        if ($this->_options['optional']) {
                        $value['enabled'] = $requestvalue != 0;
                    }
                } else {
                    $value['enabled'] = isset($value['enabled']);
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;
            case 'createElement':
                if ($arg[2]['optional']) {
                                                            if ($this->_usedcreateelement) {
                        $caller->disabledIf($arg[0] . '[day]', $arg[0] . '[enabled]');
                        $caller->disabledIf($arg[0] . '[month]', $arg[0] . '[enabled]');
                        $caller->disabledIf($arg[0] . '[year]', $arg[0] . '[enabled]');
                        $caller->disabledIf($arg[0] . '[hour]', $arg[0] . '[enabled]');
                        $caller->disabledIf($arg[0] . '[minute]', $arg[0] . '[enabled]');
                    } else {
                        $caller->disabledIf($arg[0], $arg[0] . '[enabled]');
                    }
                }
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;
            case 'addElement':
                $this->_usedcreateelement = false;
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

        $html = $this->_wrap[0];
        if ($this->_usedcreateelement) {
            $html .= html_writer::tag('span', $renderer->toHtml(), array('class' => 'fdate_time_selector'));
        } else {
            $html .= $renderer->toHtml();
        }
        $html .= $this->_wrap[1];

        return $html;
    }

    
    function accept(&$renderer, $required = false, $error = null) {
        $renderer->renderElement($this, $required, $error);
    }

    
    function exportValue(&$submitValues, $assoc = false) {
        $valuearray = array();
        foreach ($this->_elements as $element){
            $thisexport = $element->exportValue($submitValues[$this->getName()], true);
            if ($thisexport!=null){
                $valuearray += $thisexport;
            }
        }
        if (count($valuearray)){
            if($this->_options['optional']) {
                                if(empty($valuearray['enabled'])) {
                    return $this->_prepareValue(0, $assoc);
                }
            }
                        $calendartype = \core_calendar\type_factory::get_calendar_instance();
            $gregoriandate = $calendartype->convert_to_gregorian($valuearray['year'],
                                                                 $valuearray['month'],
                                                                 $valuearray['day'],
                                                                 $valuearray['hour'],
                                                                 $valuearray['minute']);
            $value = make_timestamp($gregoriandate['year'],
                                                      $gregoriandate['month'],
                                                      $gregoriandate['day'],
                                                      $gregoriandate['hour'],
                                                      $gregoriandate['minute'],
                                                      0,
                                                      $this->_options['timezone'],
                                                      true);

            return $this->_prepareValue($value, $assoc);
        } else {
            return null;
        }
    }
}
