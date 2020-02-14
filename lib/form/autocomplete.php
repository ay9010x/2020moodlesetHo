<?php




global $CFG;

require_once($CFG->libdir . '/form/select.php');


class MoodleQuickForm_autocomplete extends MoodleQuickForm_select {

    
    protected $tags = false;
    
    protected $ajax = '';
    
    protected $placeholder = '';
    
    protected $casesensitive = false;
    
    protected $showsuggestions = true;
    
    protected $noselectionstring = '';

    
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
                $this->_options = array();
        if ($attributes === null) {
            $attributes = array();
        }
        if (isset($attributes['tags'])) {
            $this->tags = $attributes['tags'];
            unset($attributes['tags']);
        }
        if (isset($attributes['showsuggestions'])) {
            $this->showsuggestions = $attributes['showsuggestions'];
            unset($attributes['showsuggestions']);
        }
        $this->placeholder = get_string('search');
        if (isset($attributes['placeholder'])) {
            $this->placeholder = $attributes['placeholder'];
            unset($attributes['placeholder']);
        }
        $this->noselectionstring = get_string('noselection', 'form');
        if (isset($attributes['noselectionstring'])) {
            $this->noselectionstring = $attributes['noselectionstring'];
            unset($attributes['noselectionstring']);
        }

        if (isset($attributes['ajax'])) {
            $this->ajax = $attributes['ajax'];
            unset($attributes['ajax']);
        }
        if (isset($attributes['casesensitive'])) {
            $this->casesensitive = $attributes['casesensitive'] ? true : false;
            unset($attributes['casesensitive']);
        }
        parent::__construct($elementName, $elementLabel, $options, $attributes);

        $this->_type = 'autocomplete';
    }

    
    public function MoodleQuickForm_autocomplete($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    function toHtml(){
        global $PAGE;

                $this->_generateId();
        $id = $this->getAttribute('id');

        if (!$this->isFrozen()) {
            $PAGE->requires->js_call_amd('core/form-autocomplete', 'enhance', $params = array('#' . $id, $this->tags, $this->ajax,
                $this->placeholder, $this->casesensitive, $this->showsuggestions, $this->noselectionstring));
        }

        return parent::toHTML();
    }

    
    function optionExists($value) {
        foreach ($this->_options as $option) {
            if (isset($option['attr']['value']) && ($option['attr']['value'] == $value)) {
                return true;
            }
        }
        return false;
    }

    
    function setValue($value) {
        $values = (array) $value;
        foreach ($values as $onevalue) {
            if (($this->tags || $this->ajax) &&
                    (!$this->optionExists($onevalue)) &&
                    ($onevalue !== '_qf__force_multiselect_submission')) {
                $this->addOption($onevalue, $onevalue);
            }
        }
        return parent::setValue($value);
    }

    
    function exportValue(&$submitValues, $assoc = false) {
        if ($this->ajax || $this->tags) {
                        $value = $this->_findValue($submitValues);
            if (null === $value) {
                $value = $this->getValue();
            }
                                                            if ($value === '_qf__force_multiselect_submission' || $value === null) {
                $value = '';
            }
            return $this->_prepareValue($value, $assoc);
        } else {
            return parent::exportValue($submitValues, $assoc);
        }
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                $caller->setType($arg[0], PARAM_TAGLIST);
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }
}
