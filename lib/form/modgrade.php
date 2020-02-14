<?php




global $CFG;
require_once "$CFG->libdir/form/select.php";
require_once("HTML/QuickForm/element.php");
require_once($CFG->dirroot.'/lib/form/group.php');
require_once($CFG->dirroot.'/lib/grade/grade_scale.php');


class MoodleQuickForm_modgrade extends MoodleQuickForm_group {

    
    public $isupdate = false;

    
    public $currentgrade = false;

    
    public $hasgrades = false;

    
    public $canrescale = false;

    
    public $currentscaleid = null;

    
    public $currentgradetype = 'none';

    
    public $useratings = false;

    
    private $gradetypeformelement;

    
    private $scaleformelement;

    
    private $maxgradeformelement;

    
    public function __construct($elementname = null, $elementlabel = null, $options = array(), $attributes = null) {
                HTML_QuickForm_element::__construct($elementname, $elementlabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'modgrade';
        $this->isupdate = !empty($options['isupdate']);
        if (isset($options['currentgrade'])) {
            $this->currentgrade = $options['currentgrade'];
        }
        if (isset($options['currentgradetype'])) {
            $gradetype = $options['currentgradetype'];
            switch ($gradetype) {
                case GRADE_TYPE_NONE :
                    $this->currentgradetype = 'none';
                    break;
                case GRADE_TYPE_SCALE :
                    $this->currentgradetype = 'scale';
                    break;
                case GRADE_TYPE_VALUE :
                    $this->currentgradetype = 'point';
                    break;
            }
        }
        if (isset($options['currentscaleid'])) {
            $this->currentscaleid = $options['currentscaleid'];
        }
        $this->hasgrades = !empty($options['hasgrades']);
        $this->canrescale = !empty($options['canrescale']);
        $this->useratings = !empty($options['useratings']);
    }

    
    public function MoodleQuickForm_modgrade($elementname = null, $elementlabel = null, $options = array(), $attributes = null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementname, $elementlabel, $options, $attributes);
    }

    
    public function _createElements() {
        global $COURSE, $CFG, $OUTPUT;
        $attributes = $this->getAttributes();
        if (is_null($attributes)) {
            $attributes = array();
        }

        $this->_elements = array();

                
                $scales = get_scales_menu($COURSE->id);
        $langscale = get_string('modgradetypescale', 'grades');
        $this->scaleformelement = @MoodleQuickForm::createElement('select', 'modgrade_scale', $langscale,
            $scales, $attributes);
        $this->scaleformelement->setHiddenLabel = false;
        $scaleformelementid = $this->generate_modgrade_subelement_id('modgrade_scale');
        $this->scaleformelement->updateAttributes(array('id' => $scaleformelementid));

                $langmaxgrade = get_string('modgrademaxgrade', 'grades');
        $this->maxgradeformelement = @MoodleQuickForm::createElement('text', 'modgrade_point', $langmaxgrade, array());
        $this->maxgradeformelement->setHiddenLabel = false;
        $maxgradeformelementid = $this->generate_modgrade_subelement_id('modgrade_point');
        $this->maxgradeformelement->updateAttributes(array('id' => $maxgradeformelementid));

                $gradetype = array(
            'none' => get_string('modgradetypenone', 'grades'),
            'scale' => get_string('modgradetypescale', 'grades'),
            'point' => get_string('modgradetypepoint', 'grades'),
        );
        $langtype = get_string('modgradetype', 'grades');
        $this->gradetypeformelement = @MoodleQuickForm::createElement('select', 'modgrade_type', $langtype, $gradetype,
            $attributes, true);
        $this->gradetypeformelement->setHiddenLabel = false;
        $gradetypeformelementid = $this->generate_modgrade_subelement_id('modgrade_type');
        $this->gradetypeformelement->updateAttributes(array('id' => $gradetypeformelementid));

        if ($this->isupdate && $this->hasgrades) {
            $this->gradetypeformelement->updateAttributes(array('disabled' => 'disabled'));
            $this->scaleformelement->updateAttributes(array('disabled' => 'disabled'));

                        if ($this->canrescale) {
                $langrescalegrades = get_string('modgraderescalegrades', 'grades');
                $choices = array();
                $choices[''] = get_string('choose');
                $choices['no'] = get_string('no');
                $choices['yes'] = get_string('yes');
                $rescalegradesselect = @MoodleQuickForm::createElement('select',
                    'modgrade_rescalegrades',
                    $langrescalegrades,
                    $choices);
                $rescalegradesselect->setHiddenLabel = false;
                $rescalegradesselectid = $this->generate_modgrade_subelement_id('modgrade_rescalegrades');
                $rescalegradesselect->updateAttributes(array('id' => $rescalegradesselectid));
            }
        }

                if ($this->isupdate && $this->hasgrades) {
                        if ($this->currentgradetype == 'scale') {
                $gradesexistmsg = get_string('modgradecantchangegradetyporscalemsg', 'grades');
            } else {
                $gradesexistmsg = get_string('modgradecantchangegradetypemsg', 'grades');
            }

            $gradesexisthtml = '<div class=\'alert\'>' . $gradesexistmsg . '</div>';
            $this->_elements[] = @MoodleQuickForm::createElement('static', 'gradesexistmsg', '', $gradesexisthtml);
        }

                $label = html_writer::tag('label', $this->gradetypeformelement->getLabel(),
            array('for' => $this->gradetypeformelement->getAttribute('id')));
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'gradetypelabel', '', '&nbsp;'.$label);
        $this->_elements[] = $this->gradetypeformelement;
        $this->_elements[] = @MoodleQuickForm::createElement('static', 'gradetypespacer', '', '<br />');

                if (!$this->isupdate || !$this->hasgrades || $this->currentgradetype == 'scale') {
            $label = html_writer::tag('label', $this->scaleformelement->getLabel(),
                array('for' => $this->scaleformelement->getAttribute('id')));
            $this->_elements[] = @MoodleQuickForm::createElement('static', 'scalelabel', '', $label);
            $this->_elements[] = $this->scaleformelement;
            $this->_elements[] = @MoodleQuickForm::createElement('static', 'scalespacer', '', '<br />');
        }

        if ($this->isupdate && $this->hasgrades && $this->canrescale && $this->currentgradetype == 'point') {
                        $label = html_writer::tag('label', $rescalegradesselect->getLabel(),
                array('for' => $rescalegradesselect->getAttribute('id')));
            $labelhelp = new help_icon('modgraderescalegrades', 'grades');
            $this->_elements[] = @MoodleQuickForm::createElement('static', 'scalelabel', '', $label . $OUTPUT->render($labelhelp));
            $this->_elements[] = $rescalegradesselect;
            $this->_elements[] = @MoodleQuickForm::createElement('static', 'scalespacer', '', '<br />');
        }

                if (!$this->isupdate || !$this->hasgrades || $this->currentgradetype == 'point') {
            $label = html_writer::tag('label', $this->maxgradeformelement->getLabel(),
                array('for' => $this->maxgradeformelement->getAttribute('id')));
            $this->_elements[] = @MoodleQuickForm::createElement('static', 'pointlabel', '', $label);
            $this->_elements[] = $this->maxgradeformelement;
            $this->_elements[] = @MoodleQuickForm::createElement('static', 'pointspacer', '', '<br />');
        }
    }

    
    public function exportValue(&$submitvalues, $notused = false) {
        global $COURSE;

                $vals = array();
        foreach ($this->_elements as $element) {
            $thisexport = $element->exportValue($submitvalues[$this->getName()], true);
            if (!is_null($thisexport)) {
                $vals += $thisexport;
            }
        }

        $type = (isset($vals['modgrade_type'])) ? $vals['modgrade_type'] : 'none';
        $point = (isset($vals['modgrade_point'])) ? $vals['modgrade_point'] : null;
        $scale = (isset($vals['modgrade_scale'])) ? $vals['modgrade_scale'] : null;
        $rescalegrades = (isset($vals['modgrade_rescalegrades'])) ? $vals['modgrade_rescalegrades'] : null;

        $return = $this->process_value($type, $scale, $point, $rescalegrades);
        return array($this->getName() => $return, $this->getName() . '_rescalegrades' => $rescalegrades);
    }

    
    protected function process_value($type='none', $scale=null, $point=null, $rescalegrades=null) {
        global $COURSE;
        $val = 0;
        if ($this->isupdate && $this->hasgrades && $this->canrescale && $this->currentgradetype == 'point' && empty($rescalegrades)) {
                                    return (string)unformat_float($this->currentgrade);
        }
        switch ($type) {
            case 'point':
                if ($this->validate_point($point) === true) {
                    $val = (int)$point;
                }
                break;

            case 'scale':
                if ($this->validate_scale($scale)) {
                    $val = (int)(-$scale);
                }
                break;
        }
        return $val;
    }

    
    protected function validate_scale($val) {
        global $COURSE;
        $scales = get_scales_menu($COURSE->id);
        return (!empty($val) && isset($scales[(int)$val])) ? true : false;
    }

    
    protected function validate_point($val) {
        if (empty($val)) {
            return false;
        }
        $maxgrade = (int)get_config('core', 'gradepointmax');
        $isintlike = ((string)(int)$val === $val) ? true : false;
        return ($isintlike === true && $val > 0 && $val <= $maxgrade) ? true : false;
    }

    
    public function onQuickFormEvent($event, $arg, &$caller) {
        switch ($event) {
            case 'createElement':
                                $name = $arg[0];

                                $caller->disabledIf($name.'[modgrade_scale]', $name.'[modgrade_type]', 'neq', 'scale');
                $caller->disabledIf($name.'[modgrade_point]', $name.'[modgrade_type]', 'neq', 'point');
                $caller->disabledIf($name.'[modgrade_rescalegrades]', $name.'[modgrade_type]', 'neq', 'point');

                                                                                $checkgradetypechange = function($val) {
                                        if (!$this->hasgrades) {
                        return true;
                    }
                                        if (isset($val['modgrade_type']) && $val['modgrade_type'] !== $this->currentgradetype) {
                        return false;
                    }
                    return true;
                };
                $checkscalechange = function($val) {
                                        if (!$this->hasgrades) {
                        return true;
                    }
                                                            $gradetype = isset($val['modgrade_type']) ? $val['modgrade_type'] : $this->currentgradetype;
                    if ($gradetype === 'scale') {
                        if (isset($val['modgrade_scale']) && ($val['modgrade_scale'] !== $this->currentscaleid)) {
                            return false;
                        }
                    }
                    return true;
                };
                $checkmaxgradechange = function($val) {
                                        if (!$this->hasgrades) {
                        return true;
                    }
                                        if (!$this->useratings) {
                        return true;
                    }
                                                            $gradetype = isset($val['modgrade_type']) ? $val['modgrade_type'] : $this->currentgradetype;
                    if ($gradetype === 'point') {
                        if (isset($val['modgrade_point']) &&
                            grade_floats_different($this->currentgrade, $val['modgrade_point'])) {
                            return false;
                        }
                    }
                    return true;
                };
                $checkmaxgrade = function($val) {
                                                            $gradetype = isset($val['modgrade_type']) ? $val['modgrade_type'] : $this->currentgradetype;
                    if ($gradetype === 'point') {
                        if (isset($val['modgrade_point'])) {
                            return $this->validate_point($val['modgrade_point']);
                        }
                    }
                    return true;
                };
                $checkvalidscale = function($val) {
                                                            $gradetype = isset($val['modgrade_type']) ? $val['modgrade_type'] : $this->currentgradetype;
                    if ($gradetype === 'scale') {
                        if (isset($val['modgrade_scale'])) {
                            return $this->validate_scale($val['modgrade_scale']);
                        }
                    }
                    return true;
                };

                $checkrescale = function($val) {
                                        if (!$this->isupdate || !$this->hasgrades || !$this->canrescale) {
                        return true;
                    }
                                                            $gradetype = isset($val['modgrade_type']) ? $val['modgrade_type'] : $this->currentgradetype;
                    if ($gradetype === 'point' && isset($val['modgrade_point'])) {
                                                if (grade_floats_different($this->currentgrade, $val['modgrade_point'])) {
                            if (empty($val['modgrade_rescalegrades'])) {
                                                                return false;
                            }
                        }
                    }
                    return true;
                };

                $cantchangegradetype = get_string('modgradecantchangegradetype', 'grades');
                $cantchangemaxgrade = get_string('modgradecantchangeratingmaxgrade', 'grades');
                $maxgradeexceeded = get_string('modgradeerrorbadpoint', 'grades', get_config('core', 'gradepointmax'));
                $invalidscale = get_string('modgradeerrorbadscale', 'grades');
                $cantchangescale = get_string('modgradecantchangescale', 'grades');
                $mustchooserescale = get_string('mustchooserescaleyesorno', 'grades');
                                                                $caller->addRule($name, $cantchangegradetype, 'callback', $checkgradetypechange, 'server', false, true);
                $caller->addRule($name, $cantchangemaxgrade, 'callback', $checkmaxgradechange, 'server', false, true);
                $caller->addRule($name, $maxgradeexceeded, 'callback', $checkmaxgrade, 'server', false, true);
                $caller->addRule($name, $invalidscale, 'callback', $checkvalidscale, 'server', false, true);
                $caller->addRule($name, $cantchangescale, 'callback', $checkscalechange, 'server', false, true);
                $caller->addRule($name, $mustchooserescale, 'callback', $checkrescale, 'server', false, true);

                break;

            case 'updateValue':
                                                                
                                $caller->disabledIf($this->getName() . '[modgrade_point]', $this->getName() .
                        '[modgrade_rescalegrades]', 'eq', '');

                                                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    if ($caller->isSubmitted() && $this->_findValue($caller->_submitValues) !== null) {
                                                                        break;
                    }
                    $value = $this->_findValue($caller->_defaultValues);
                }

                if (!is_null($value) && !is_scalar($value)) {
                                                            debugging('An invalid value (type '.gettype($value).') has arrived at '.__METHOD__, DEBUG_DEVELOPER);
                    break;
                }

                                                                                                if (!empty($this->_elements)) {
                    if (!empty($value)) {
                        if ($value < 0) {
                            $this->gradetypeformelement->setValue('scale');
                            $this->scaleformelement->setValue(($value * -1));
                        } else if ($value > 0) {
                            $this->gradetypeformelement->setValue('point');
                            $maxvalue = !empty($this->currentgrade) ? (string)unformat_float($this->currentgrade) : $value;
                            $this->maxgradeformelement->setValue($maxvalue);
                        }
                    } else {
                        $this->gradetypeformelement->setValue('none');
                        $this->maxgradeformelement->setValue('');
                    }
                }
                break;
        }

                return parent::onQuickFormEvent($event, $arg, $caller);
    }

    
    protected function generate_modgrade_subelement_id($subname) {
        $gid = str_replace(array('[', ']'), array('_', ''), $this->getName());
        return clean_param('id_'.$gid.'_'.$subname, PARAM_ALPHANUMEXT);
    }
}
