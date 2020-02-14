<?php



defined('MOODLE_INTERNAL') || die();

require_once("HTML/QuickForm/input.php");


class MoodleQuickForm_rubriceditor extends HTML_QuickForm_input {
    
    public $_helpbutton = '';
    
    protected $validationerrors = null;
    
    protected $wasvalidated = false;
    
    protected $nonjsbuttonpressed = false;
    
    protected $regradeconfirmation = false;

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_rubriceditor($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function getHelpButton() {
        return $this->_helpbutton;
    }

    
    public function getElementTemplateType() {
        return 'default';
    }

    
    public function add_regrade_confirmation($changelevel) {
        $this->regradeconfirmation = $changelevel;
    }

    
    public function toHtml() {
        global $PAGE;
        $html = $this->_getTabs();
        $renderer = $PAGE->get_renderer('gradingform_rubric');
        $data = $this->prepare_data(null, $this->wasvalidated);
        if (!$this->_flagFrozen) {
            $mode = gradingform_rubric_controller::DISPLAY_EDIT_FULL;
            $module = array('name'=>'gradingform_rubriceditor', 'fullpath'=>'/grade/grading/form/rubric/js/rubriceditor.js',
                'requires' => array('base', 'dom', 'event', 'event-touch', 'escape'),
                'strings' => array(array('confirmdeletecriterion', 'gradingform_rubric'), array('confirmdeletelevel', 'gradingform_rubric'),
                    array('criterionempty', 'gradingform_rubric'), array('levelempty', 'gradingform_rubric')
                ));
            $PAGE->requires->js_init_call('M.gradingform_rubriceditor.init', array(
                array('name' => $this->getName(),
                    'criteriontemplate' => $renderer->criterion_template($mode, $data['options'], $this->getName()),
                    'leveltemplate' => $renderer->level_template($mode, $data['options'], $this->getName())
                   )),
                true, $module);
        } else {
                        if ($this->_persistantFreeze) {
                $mode = gradingform_rubric_controller::DISPLAY_EDIT_FROZEN;
            } else {
                $mode = gradingform_rubric_controller::DISPLAY_PREVIEW;
            }
        }
        if ($this->regradeconfirmation) {
            if (!isset($data['regrade'])) {
                $data['regrade'] = 1;
            }
            $html .= $renderer->display_regrade_confirmation($this->getName(), $this->regradeconfirmation, $data['regrade']);
        }
        if ($this->validationerrors) {
            $html .= html_writer::div($renderer->notification($this->validationerrors));
        }
        $html .= $renderer->display_rubric($data['criteria'], $data['options'], $mode, $this->getName());
        return $html;
    }

    
    protected function prepare_data($value = null, $withvalidation = false) {
        if (null === $value) {
            $value = $this->getValue();
        }
        if ($this->nonjsbuttonpressed === null) {
            $this->nonjsbuttonpressed = false;
        }
        $totalscore = 0;
        $errors = array();
        $return = array('criteria' => array(), 'options' => gradingform_rubric_controller::get_default_options());
        if (!isset($value['criteria'])) {
            $value['criteria'] = array();
            $errors['err_nocriteria'] = 1;
        }
                if (!empty($value['options'])) {
            foreach (array_keys($return['options']) as $option) {
                                if (!empty($value['options'][$option])) {
                    $return['options'][$option] = $value['options'][$option];
                } else {
                    $return['options'][$option] = null;
                }
            }
        }
        if (is_array($value)) {
                        foreach (array_keys($value) as $key) {
                if ($key != 'options' && $key != 'criteria') {
                    $return[$key] = $value[$key];
                }
            }
        }

                $lastaction = null;
        $lastid = null;
        $overallminscore = $overallmaxscore = 0;
        foreach ($value['criteria'] as $id => $criterion) {
            if ($id == 'addcriterion') {
                $id = $this->get_next_id(array_keys($value['criteria']));
                $criterion = array('description' => '', 'levels' => array());
                $i = 0;
                                if (!empty($value['criteria'][$lastid]['levels'])) {
                    foreach ($value['criteria'][$lastid]['levels'] as $lastlevel) {
                        $criterion['levels']['NEWID'.($i++)]['score'] = $lastlevel['score'];
                    }
                } else {
                    $criterion['levels']['NEWID'.($i++)]['score'] = 0;
                }
                                for ($i=$i; $i<3; $i++) {
                    $criterion['levels']['NEWID'.$i]['score'] = $criterion['levels']['NEWID'.($i-1)]['score'] + 1;
                }
                                foreach (array_keys($criterion['levels']) as $i) {
                    $criterion['levels'][$i]['definition'] = '';
                }
                $this->nonjsbuttonpressed = true;
            }
            $levels = array();
            $minscore = $maxscore = null;
            if (array_key_exists('levels', $criterion)) {
                foreach ($criterion['levels'] as $levelid => $level) {
                    if ($levelid == 'addlevel') {
                        $levelid = $this->get_next_id(array_keys($criterion['levels']));
                        $level = array(
                            'definition' => '',
                            'score' => 0,
                        );
                        foreach ($criterion['levels'] as $lastlevel) {
                            if (isset($lastlevel['score']) && $level['score'] < $lastlevel['score'] + 1) {
                                $level['score'] = $lastlevel['score'] + 1;
                            }
                        }
                        $this->nonjsbuttonpressed = true;
                    }
                    if (!array_key_exists('delete', $level)) {
                        if ($withvalidation) {
                            if (!strlen(trim($level['definition']))) {
                                $errors['err_nodefinition'] = 1;
                                $level['error_definition'] = true;
                            }
                            if (!preg_match('#^[\+]?\d*$#', trim($level['score'])) && !preg_match('#^[\+]?\d*[\.,]\d+$#', trim($level['score']))) {
                                $errors['err_scoreformat'] = 1;
                                $level['error_score'] = true;
                            }
                        }
                        $levels[$levelid] = $level;
                        if ($minscore === null || (float)$level['score'] < $minscore) {
                            $minscore = (float)$level['score'];
                        }
                        if ($maxscore === null || (float)$level['score'] > $maxscore) {
                            $maxscore = (float)$level['score'];
                        }
                    } else {
                        $this->nonjsbuttonpressed = true;
                    }
                }
            }
            $totalscore += (float)$maxscore;
            $criterion['levels'] = $levels;
            if ($withvalidation && !array_key_exists('delete', $criterion)) {
                if (count($levels)<2) {
                    $errors['err_mintwolevels'] = 1;
                    $criterion['error_levels'] = true;
                }
                if (!strlen(trim($criterion['description']))) {
                    $errors['err_nodescription'] = 1;
                    $criterion['error_description'] = true;
                }
                $overallmaxscore += $maxscore;
                $overallminscore += $minscore;
            }
            if (array_key_exists('moveup', $criterion) || $lastaction == 'movedown') {
                unset($criterion['moveup']);
                if ($lastid !== null) {
                    $lastcriterion = $return['criteria'][$lastid];
                    unset($return['criteria'][$lastid]);
                    $return['criteria'][$id] = $criterion;
                    $return['criteria'][$lastid] = $lastcriterion;
                } else {
                    $return['criteria'][$id] = $criterion;
                }
                $lastaction = null;
                $lastid = $id;
                $this->nonjsbuttonpressed = true;
            } else if (array_key_exists('delete', $criterion)) {
                $this->nonjsbuttonpressed = true;
            } else {
                if (array_key_exists('movedown', $criterion)) {
                    unset($criterion['movedown']);
                    $lastaction = 'movedown';
                    $this->nonjsbuttonpressed = true;
                }
                $return['criteria'][$id] = $criterion;
                $lastid = $id;
            }
        }

        if ($totalscore <= 0) {
            $errors['err_totalscore'] = 1;
        }

                $csortorder = 1;
        foreach (array_keys($return['criteria']) as $id) {
            $return['criteria'][$id]['sortorder'] = $csortorder++;
        }

                if ($withvalidation) {
            if ($overallminscore == $overallmaxscore) {
                $errors['err_novariations'] = 1;
            }
            if (count($errors)) {
                $rv = array();
                foreach ($errors as $error => $v) {
                    $rv[] = get_string($error, 'gradingform_rubric');
                }
                $this->validationerrors = join('<br/ >', $rv);
            } else {
                $this->validationerrors = false;
            }
            $this->wasvalidated = true;
        }
        return $return;
    }

    
    protected function get_next_id($ids) {
        $maxid = 0;
        foreach ($ids as $id) {
            if (preg_match('/^NEWID(\d+)$/', $id, $matches) && ((int)$matches[1]) > $maxid) {
                $maxid = (int)$matches[1];
            }
        }
        return 'NEWID'.($maxid+1);
    }

    
    public function non_js_button_pressed($value) {
        if ($this->nonjsbuttonpressed === null) {
            $this->prepare_data($value);
        }
        return $this->nonjsbuttonpressed;
    }

    
    public function validate($value) {
        if (!$this->wasvalidated) {
            $this->prepare_data($value, true);
        }
        return $this->validationerrors;
    }

    
    public function exportValue(&$submitValues, $assoc = false) {
        $value =  $this->prepare_data($this->_findValue($submitValues));
        return $this->_prepareValue($value, $assoc);
    }
}
