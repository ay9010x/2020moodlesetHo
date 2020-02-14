<?php



defined('MOODLE_INTERNAL') || die();

require_once("HTML/QuickForm/input.php");


class moodlequickform_guideeditor extends HTML_QuickForm_input {
    
    public $_helpbutton = '';
    
    protected $validationerrors = null;
    
    protected $wasvalidated = false;
    
    protected $nonjsbuttonpressed = false;
    
    protected $regradeconfirmation = false;

    
    public function __construct($elementname=null, $elementlabel=null, $attributes=null) {
        parent::__construct($elementname, $elementlabel, $attributes);
    }

    
    public function moodlequickform_guideeditor($elementname=null, $elementlabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementname, $elementlabel, $attributes);
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
        $renderer = $PAGE->get_renderer('gradingform_guide');
        $data = $this->prepare_data(null, $this->wasvalidated);
        if (!$this->_flagFrozen) {
            $mode = gradingform_guide_controller::DISPLAY_EDIT_FULL;
            $module = array('name'=>'gradingform_guideeditor',
                'fullpath'=>'/grade/grading/form/guide/js/guideeditor.js',
                'requires' => array('base', 'dom', 'event', 'event-touch', 'escape'),
                'strings' => array(
                    array('confirmdeletecriterion', 'gradingform_guide'),
                    array('clicktoedit', 'gradingform_guide'),
                    array('clicktoeditname', 'gradingform_guide')
            ));
            $PAGE->requires->js_init_call('M.gradingform_guideeditor.init', array(
                array('name' => $this->getName(),
                    'criteriontemplate' => $renderer->criterion_template($mode, $data['options'], $this->getName()),
                    'commenttemplate' => $renderer->comment_template($mode, $this->getName())
                   )),
                true, $module);
        } else {
                        if ($this->_persistantFreeze) {
                $mode = gradingform_guide_controller::DISPLAY_EDIT_FROZEN;
            } else {
                $mode = gradingform_guide_controller::DISPLAY_PREVIEW;
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
        $html .= $renderer->display_guide($data['criteria'], $data['comments'], $data['options'], $mode, $this->getName());
        return $html;
    }
    
    protected function prepare_data($value = null, $withvalidation = false) {
        if (null === $value) {
            $value = $this->getValue();
        }
        if ($this->nonjsbuttonpressed === null) {
            $this->nonjsbuttonpressed = false;
        }

        $errors = array();
        $return = array('criteria' => array(), 'options' => gradingform_guide_controller::get_default_options(),
            'comments' => array());
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
                if ($key != 'options' && $key != 'criteria' && $key != 'comments') {
                    $return[$key] = $value[$key];
                }
            }
        }

                $lastaction = null;
        $lastid = null;
        foreach ($value['criteria'] as $id => $criterion) {
            if ($id == 'addcriterion') {
                $id = $this->get_next_id(array_keys($value['criteria']));
                $criterion = array('description' => '');
                $this->nonjsbuttonpressed = true;
            }

            if ($withvalidation && !array_key_exists('delete', $criterion)) {
                if (!strlen(trim($criterion['shortname']))) {
                    $errors['err_noshortname'] = 1;
                    $criterion['error_description'] = true;
                }
                if (strlen(trim($criterion['shortname'])) > 255) {
                    $errors['err_shortnametoolong'] = 1;
                    $criterion['error_description'] = true;
                }
                if (!strlen(trim($criterion['maxscore']))) {
                    $errors['err_nomaxscore'] = 1;
                    $criterion['error_description'] = true;
                } else if (!is_numeric($criterion['maxscore'])) {
                    $errors['err_maxscorenotnumeric'] = 1;
                    $criterion['error_description'] = true;
                } else if ($criterion['maxscore'] < 0) {
                    $errors['err_maxscoreisnegative'] = 1;
                    $criterion['error_description'] = true;
                }
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

                $csortorder = 1;
        foreach (array_keys($return['criteria']) as $id) {
            $return['criteria'][$id]['sortorder'] = $csortorder++;
        }

                $lastaction = null;
        $lastid = null;
        if (!empty($value['comments'])) {
            foreach ($value['comments'] as $id => $comment) {
                if ($id == 'addcomment') {
                    $id = $this->get_next_id(array_keys($value['comments']));
                    $comment = array('description' => '');
                    $this->nonjsbuttonpressed = true;
                }

                if (array_key_exists('moveup', $comment) || $lastaction == 'movedown') {
                    unset($comment['moveup']);
                    if ($lastid !== null) {
                        $lastcomment = $return['comments'][$lastid];
                        unset($return['comments'][$lastid]);
                        $return['comments'][$id] = $comment;
                        $return['comments'][$lastid] = $lastcomment;
                    } else {
                        $return['comments'][$id] = $comment;
                    }
                    $lastaction = null;
                    $lastid = $id;
                    $this->nonjsbuttonpressed = true;
                } else if (array_key_exists('delete', $comment)) {
                    $this->nonjsbuttonpressed = true;
                } else {
                    if (array_key_exists('movedown', $comment)) {
                        unset($comment['movedown']);
                        $lastaction = 'movedown';
                        $this->nonjsbuttonpressed = true;
                    }
                    $return['comments'][$id] = $comment;
                    $lastid = $id;
                }
            }
                        $csortorder = 1;
            foreach (array_keys($return['comments']) as $id) {
                $return['comments'][$id]['sortorder'] = $csortorder++;
            }
        }
                if ($withvalidation) {
            if (count($errors)) {
                $rv = array();
                foreach ($errors as $error => $v) {
                    $rv[] = get_string($error, 'gradingform_guide');
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

    
    public function exportValue(&$submitvalues, $assoc = false) {
        $value =  $this->prepare_data($this->_findValue($submitvalues));
        return $this->_prepareValue($value, $assoc);
    }
}
