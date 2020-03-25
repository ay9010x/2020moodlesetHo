<?php



defined('MOODLE_INTERNAL') || die();


class gradingform_rubric_renderer extends plugin_renderer_base {

    
    public function criterion_template($mode, $options, $elementname = '{NAME}', $criterion = null, $levelsstr = '{LEVELS}', $value = null) {
                if ($criterion === null || !is_array($criterion) || !array_key_exists('id', $criterion)) {
            $criterion = array('id' => '{CRITERION-id}', 'description' => '{CRITERION-description}', 'sortorder' => '{CRITERION-sortorder}', 'class' => '{CRITERION-class}');
        } else {
            foreach (array('sortorder', 'description', 'class') as $key) {
                                if (!array_key_exists($key, $criterion)) {
                    $criterion[$key] = '';
                }
            }
        }
        $criteriontemplate = html_writer::start_tag('tr', array('class' => 'criterion'. $criterion['class'], 'id' => '{NAME}-criteria-{CRITERION-id}'));
        if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FULL) {
            $criteriontemplate .= html_writer::start_tag('td', array('class' => 'controls'));
            foreach (array('moveup', 'delete', 'movedown', 'duplicate') as $key) {
                $value = get_string('criterion'.$key, 'gradingform_rubric');
                $button = html_writer::empty_tag('input', array('type' => 'submit', 'name' => '{NAME}[criteria][{CRITERION-id}]['.$key.']',
                    'id' => '{NAME}-criteria-{CRITERION-id}-'.$key, 'value' => $value));
                $criteriontemplate .= html_writer::tag('div', $button, array('class' => $key));
            }
            $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                                                                        'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]',
                                                                        'value' => $criterion['sortorder']));
            $criteriontemplate .= html_writer::end_tag('td'); 
                        $descriptiontextareaparams = array(
                'name' => '{NAME}[criteria][{CRITERION-id}][description]',
                'id' => '{NAME}-criteria-{CRITERION-id}-description',
                'aria-label' => get_string('criterion', 'gradingform_rubric', ''),
                'cols' => '10', 'rows' => '5'
            );
            $description = html_writer::tag('textarea', s($criterion['description']), $descriptiontextareaparams);
        } else {
            if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FROZEN) {
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]', 'value' => $criterion['sortorder']));
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][description]', 'value' => $criterion['description']));
            }
            $description = s($criterion['description']);
        }
        $descriptionclass = 'description';
        if (isset($criterion['error_description'])) {
            $descriptionclass .= ' error';
        }

                $descriptiontdparams = array(
            'class' => $descriptionclass,
            'id' => '{NAME}-criteria-{CRITERION-id}-description-cell'
        );
        if ($mode != gradingform_rubric_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_rubric_controller::DISPLAY_EDIT_FROZEN) {
                        $descriptiontdparams['tabindex'] = '0';
                        $descriptiontdparams['aria-label'] = get_string('criterion', 'gradingform_rubric', s($criterion['description']));
        }

                $criteriontemplate .= html_writer::tag('td', $description, $descriptiontdparams);

                $levelsrowparams = array('id' => '{NAME}-criteria-{CRITERION-id}-levels');
        if ($mode != gradingform_rubric_controller::DISPLAY_EDIT_FULL) {
            $levelsrowparams['role'] = 'radiogroup';
        }
        $levelsrow = html_writer::tag('tr', $levelsstr, $levelsrowparams);

        $levelstableparams = array(
            'id' => '{NAME}-criteria-{CRITERION-id}-levels-table',
            'aria-label' => get_string('levelsgroup', 'gradingform_rubric')
        );
        $levelsstrtable = html_writer::tag('table', $levelsrow, $levelstableparams);
        $levelsclass = 'levels';
        if (isset($criterion['error_levels'])) {
            $levelsclass .= ' error';
        }
        $criteriontemplate .= html_writer::tag('td', $levelsstrtable, array('class' => $levelsclass));
        if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('criterionaddlevel', 'gradingform_rubric');
            $button = html_writer::empty_tag('input', array('type' => 'submit', 'name' => '{NAME}[criteria][{CRITERION-id}][levels][addlevel]',
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-addlevel', 'value' => $value));
            $criteriontemplate .= html_writer::tag('td', $button, array('class' => 'addlevel'));
        }
        $displayremark = ($options['enableremarks'] && ($mode != gradingform_rubric_controller::DISPLAY_VIEW || $options['showremarksstudent']));
        if ($displayremark) {
            $currentremark = '';
            if (isset($value['remark'])) {
                $currentremark = $value['remark'];
            }

                        $remarkinfo = new stdClass();
            $remarkinfo->description = s($criterion['description']);
            $remarkinfo->remark = $currentremark;
            $remarklabeltext = get_string('criterionremark', 'gradingform_rubric', $remarkinfo);

            if ($mode == gradingform_rubric_controller::DISPLAY_EVAL) {
                                $remarkparams = array(
                    'name' => '{NAME}[criteria][{CRITERION-id}][remark]',
                    'id' => '{NAME}-criteria-{CRITERION-id}-remark',
                    'cols' => '10', 'rows' => '5',
                    'aria-label' => $remarklabeltext
                );
                $input = html_writer::tag('textarea', s($currentremark), $remarkparams);
                $criteriontemplate .= html_writer::tag('td', $input, array('class' => 'remark'));
            } else if ($mode == gradingform_rubric_controller::DISPLAY_EVAL_FROZEN) {
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][remark]', 'value' => $currentremark));
            }else if ($mode == gradingform_rubric_controller::DISPLAY_REVIEW || $mode == gradingform_rubric_controller::DISPLAY_VIEW) {
                                $remarkparams = array(
                    'class' => 'remark',
                    'tabindex' => '0',
                    'id' => '{NAME}-criteria-{CRITERION-id}-remark',
                    'aria-label' => $remarklabeltext
                );
                $criteriontemplate .= html_writer::tag('td', s($currentremark), $remarkparams);
            }
        }
        $criteriontemplate .= html_writer::end_tag('tr'); 
        $criteriontemplate = str_replace('{NAME}', $elementname, $criteriontemplate);
        $criteriontemplate = str_replace('{CRITERION-id}', $criterion['id'], $criteriontemplate);
        return $criteriontemplate;
    }

    
    public function level_template($mode, $options, $elementname = '{NAME}', $criterionid = '{CRITERION-id}', $level = null) {
                if (!isset($level['id'])) {
            $level = array('id' => '{LEVEL-id}', 'definition' => '{LEVEL-definition}', 'score' => '{LEVEL-score}', 'class' => '{LEVEL-class}', 'checked' => false);
        } else {
            foreach (array('score', 'definition', 'class', 'checked', 'index') as $key) {
                                if (!array_key_exists($key, $level)) {
                    $level[$key] = '';
                }
            }
        }

                $levelindex = isset($level['index']) ? $level['index'] : '{LEVEL-index}';

                $tdattributes = array(
            'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}',
            'class' => 'level' . $level['class']
        );
        if (isset($level['tdwidth'])) {
            $tdattributes['width'] = round($level['tdwidth']).'%';
        }

        $leveltemplate = html_writer::start_tag('div', array('class' => 'level-wrapper'));
        if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FULL) {
            $definitionparams = array(
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][definition]',
                'aria-label' => get_string('leveldefinition', 'gradingform_rubric', $levelindex),
                'cols' => '10', 'rows' => '4'
            );
            $definition = html_writer::tag('textarea', s($level['definition']), $definitionparams);

            $scoreparams = array(
                'type' => 'text',
                'id' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]',
                'aria-label' => get_string('scoreinputforlevel', 'gradingform_rubric', $levelindex),
                'size' => '3',
                'value' => $level['score']
            );
            $score = html_writer::empty_tag('input', $scoreparams);
        } else {
            if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FROZEN) {
                $leveltemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][definition]', 'value' => $level['definition']));
                $leveltemplate .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][score]', 'value' => $level['score']));
            }
            $definition = s($level['definition']);
            $score = $level['score'];
        }
        if ($mode == gradingform_rubric_controller::DISPLAY_EVAL) {
            $levelradioparams = array(
                'type' => 'radio',
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition',
                'name' => '{NAME}[criteria][{CRITERION-id}][levelid]',
                'value' => $level['id']
            );
            if ($level['checked']) {
                $levelradioparams['checked'] = 'checked';
            }
            $input = html_writer::empty_tag('input', $levelradioparams);
            $leveltemplate .= html_writer::div($input, 'radio');
        }
        if ($mode == gradingform_rubric_controller::DISPLAY_EVAL_FROZEN && $level['checked']) {
            $leveltemplate .= html_writer::empty_tag('input',
                array(
                    'type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][levelid]',
                    'value' => $level['id']
                )
            );
        }
        $score = html_writer::tag('span', $score, array('id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-score', 'class' => 'scorevalue'));
        $definitionclass = 'definition';
        if (isset($level['error_definition'])) {
            $definitionclass .= ' error';
        }

        if ($mode != gradingform_rubric_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_rubric_controller::DISPLAY_EDIT_FROZEN) {

            $tdattributes['tabindex'] = '0';
            $levelinfo = new stdClass();
            $levelinfo->definition = s($level['definition']);
            $levelinfo->score = $level['score'];
            $tdattributes['aria-label'] = get_string('level', 'gradingform_rubric', $levelinfo);

            if ($mode != gradingform_rubric_controller::DISPLAY_PREVIEW &&
                $mode != gradingform_rubric_controller::DISPLAY_PREVIEW_GRADED) {
                                $tdattributes['role'] = 'radio';
                if ($level['checked']) {
                    $tdattributes['aria-checked'] = 'true';
                } else {
                    $tdattributes['aria-checked'] = 'false';
                }
            }
        }

        $leveltemplateparams = array(
            'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-definition-container'
        );
        $leveltemplate .= html_writer::div($definition, $definitionclass, $leveltemplateparams);
        $displayscore = true;
        if (!$options['showscoreteacher'] && in_array($mode, array(gradingform_rubric_controller::DISPLAY_EVAL, gradingform_rubric_controller::DISPLAY_EVAL_FROZEN, gradingform_rubric_controller::DISPLAY_REVIEW))) {
            $displayscore = false;
        }
        if (!$options['showscorestudent'] && in_array($mode, array(gradingform_rubric_controller::DISPLAY_VIEW, gradingform_rubric_controller::DISPLAY_PREVIEW_GRADED))) {
            $displayscore = false;
        }
        if ($displayscore) {
            $scoreclass = 'score';
            if (isset($level['error_score'])) {
                $scoreclass .= ' error';
            }
            $leveltemplate .= html_writer::tag('div', get_string('scorepostfix', 'gradingform_rubric', $score), array('class' => $scoreclass));
        }
        if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('leveldelete', 'gradingform_rubric', $levelindex);
            $buttonparams = array(
                'type' => 'submit',
                'name' => '{NAME}[criteria][{CRITERION-id}][levels][{LEVEL-id}][delete]',
                'id' => '{NAME}-criteria-{CRITERION-id}-levels-{LEVEL-id}-delete',
                'value' => $value
            );
            $button = html_writer::empty_tag('input', $buttonparams);
            $leveltemplate .= html_writer::tag('div', $button, array('class' => 'delete'));
        }
        $leveltemplate .= html_writer::end_tag('div'); 
        $leveltemplate = html_writer::tag('td', $leveltemplate, $tdattributes); 
        $leveltemplate = str_replace('{NAME}', $elementname, $leveltemplate);
        $leveltemplate = str_replace('{CRITERION-id}', $criterionid, $leveltemplate);
        $leveltemplate = str_replace('{LEVEL-id}', $level['id'], $leveltemplate);
        return $leveltemplate;
    }

    
    protected function rubric_template($mode, $options, $elementname, $criteriastr) {
        $classsuffix = '';         switch ($mode) {
            case gradingform_rubric_controller::DISPLAY_EDIT_FULL:
                $classsuffix = ' editor editable'; break;
            case gradingform_rubric_controller::DISPLAY_EDIT_FROZEN:
                $classsuffix = ' editor frozen';  break;
            case gradingform_rubric_controller::DISPLAY_PREVIEW:
            case gradingform_rubric_controller::DISPLAY_PREVIEW_GRADED:
                $classsuffix = ' editor preview';  break;
            case gradingform_rubric_controller::DISPLAY_EVAL:
                $classsuffix = ' evaluate editable'; break;
            case gradingform_rubric_controller::DISPLAY_EVAL_FROZEN:
                $classsuffix = ' evaluate frozen';  break;
            case gradingform_rubric_controller::DISPLAY_REVIEW:
                $classsuffix = ' review';  break;
            case gradingform_rubric_controller::DISPLAY_VIEW:
                $classsuffix = ' view';  break;
        }

        $rubrictemplate = html_writer::start_tag('div', array('id' => 'rubric-{NAME}', 'class' => 'clearfix gradingform_rubric'.$classsuffix));

                $rubrictableparams = array(
            'class' => 'criteria',
            'id' => '{NAME}-criteria',
            'aria-label' => get_string('rubric', 'gradingform_rubric'));
        $rubrictable = html_writer::tag('table', $criteriastr, $rubrictableparams);
        $rubrictemplate .= $rubrictable;
        if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('addcriterion', 'gradingform_rubric');
            $criteriainputparams = array(
                'type' => 'submit',
                'name' => '{NAME}[criteria][addcriterion]',
                'id' => '{NAME}-criteria-addcriterion',
                'value' => $value
            );
            $input = html_writer::empty_tag('input', $criteriainputparams);
            $rubrictemplate .= html_writer::tag('div', $input, array('class' => 'addcriterion'));
        }
        $rubrictemplate .= $this->rubric_edit_options($mode, $options);
        $rubrictemplate .= html_writer::end_tag('div');

        return str_replace('{NAME}', $elementname, $rubrictemplate);
    }

    
    protected function rubric_edit_options($mode, $options) {
        if ($mode != gradingform_rubric_controller::DISPLAY_EDIT_FULL
                && $mode != gradingform_rubric_controller::DISPLAY_EDIT_FROZEN
                && $mode != gradingform_rubric_controller::DISPLAY_PREVIEW) {
                        return;
        }
        $html = html_writer::start_tag('div', array('class' => 'options'));
        $html .= html_writer::tag('div', get_string('rubricoptions', 'gradingform_rubric'), array('class' => 'optionsheading'));
        $attrs = array('type' => 'hidden', 'name' => '{NAME}[options][optionsset]', 'value' => 1);
        foreach ($options as $option => $value) {
            $html .= html_writer::start_tag('div', array('class' => 'option '.$option));
            $attrs = array('name' => '{NAME}[options]['.$option.']', 'id' => '{NAME}-options-'.$option);
            switch ($option) {
                case 'sortlevelsasc':
                                        $html .= html_writer::label(get_string($option, 'gradingform_rubric'), $attrs['id'], false, array('class' => 'label'));
                    $value = (int)(!!$value);                     if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FULL) {
                        $selectoptions = array(0 => get_string($option.'0', 'gradingform_rubric'), 1 => get_string($option.'1', 'gradingform_rubric'));
                        $valuestr = html_writer::select($selectoptions, $attrs['name'], $value, false, array('id' => $attrs['id']));
                        $html .= html_writer::tag('span', $valuestr, array('class' => 'value'));
                    } else {
                        $html .= html_writer::tag('span', get_string($option.$value, 'gradingform_rubric'), array('class' => 'value'));
                        if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FROZEN) {
                            $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                        }
                    }
                    break;
                default:
                    if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FROZEN && $value) {
                                                $attrs['id'] .= '_hidden';
                        $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                    }
                                        $attrs['type'] = 'checkbox';
                    $attrs['value'] = 1;
                    if ($value) {
                        $attrs['checked'] = 'checked';
                    }
                    if ($mode == gradingform_rubric_controller::DISPLAY_EDIT_FROZEN || $mode == gradingform_rubric_controller::DISPLAY_PREVIEW) {
                        $attrs['disabled'] = 'disabled';
                        unset($attrs['name']);
                                                $attrs['id'] .= '_disabled';
                    }
                    $html .= html_writer::empty_tag('input', $attrs);
                    $html .= html_writer::tag('label', get_string($option, 'gradingform_rubric'), array('for' => $attrs['id']));
                    break;
            }
            $html .= html_writer::end_tag('div');         }
        $html .= html_writer::end_tag('div');         return $html;
    }

    
    public function display_rubric($criteria, $options, $mode, $elementname = null, $values = null) {
        $criteriastr = '';
        $cnt = 0;
        foreach ($criteria as $id => $criterion) {
            $criterion['class'] = $this->get_css_class_suffix($cnt++, sizeof($criteria) -1);
            $criterion['id'] = $id;
            $levelsstr = '';
            $levelcnt = 0;
            if (isset($values['criteria'][$id])) {
                $criterionvalue = $values['criteria'][$id];
            } else {
                $criterionvalue = null;
            }
            $index = 1;
            foreach ($criterion['levels'] as $levelid => $level) {
                $level['id'] = $levelid;
                $level['class'] = $this->get_css_class_suffix($levelcnt++, sizeof($criterion['levels']) -1);
                $level['checked'] = (isset($criterionvalue['levelid']) && ((int)$criterionvalue['levelid'] === $levelid));
                if ($level['checked'] && ($mode == gradingform_rubric_controller::DISPLAY_EVAL_FROZEN || $mode == gradingform_rubric_controller::DISPLAY_REVIEW || $mode == gradingform_rubric_controller::DISPLAY_VIEW)) {
                    $level['class'] .= ' checked';
                                    }
                if (isset($criterionvalue['savedlevelid']) && ((int)$criterionvalue['savedlevelid'] === $levelid)) {
                    $level['class'] .= ' currentchecked';
                }
                $level['tdwidth'] = 100/count($criterion['levels']);
                $level['index'] = $index;
                $levelsstr .= $this->level_template($mode, $options, $elementname, $id, $level);
                $index++;
            }
            $criteriastr .= $this->criterion_template($mode, $options, $elementname, $criterion, $levelsstr, $criterionvalue);
        }
        return $this->rubric_template($mode, $options, $elementname, $criteriastr);
    }

    
    protected function get_css_class_suffix($idx, $maxidx) {
        $class = '';
        if ($idx == 0) {
            $class .= ' first';
        }
        if ($idx == $maxidx) {
            $class .= ' last';
        }
        if ($idx%2) {
            $class .= ' odd';
        } else {
            $class .= ' even';
        }
        return $class;
    }

    
    public function display_instances($instances, $defaultcontent, $cangrade) {
        $return = '';
        if (sizeof($instances)) {
            $return .= html_writer::start_tag('div', array('class' => 'advancedgrade'));
            $idx = 0;
            foreach ($instances as $instance) {
                $return .= $this->display_instance($instance, $idx++, $cangrade);
            }
            $return .= html_writer::end_tag('div');
        }
        return $return. $defaultcontent;
    }

    
    public function display_instance(gradingform_rubric_instance $instance, $idx, $cangrade) {
        $criteria = $instance->get_controller()->get_definition()->rubric_criteria;
        $options = $instance->get_controller()->get_options();
        $values = $instance->get_rubric_filling();
        if ($cangrade) {
            $mode = gradingform_rubric_controller::DISPLAY_REVIEW;
            $showdescription = $options['showdescriptionteacher'];
        } else {
            $mode = gradingform_rubric_controller::DISPLAY_VIEW;
            $showdescription = $options['showdescriptionstudent'];
        }
        $output = '';
        if ($showdescription) {
            $output .= $this->box($instance->get_controller()->get_formatted_description(), 'gradingform_rubric-description');
        }
        $output .= $this->display_rubric($criteria, $options, $mode, 'rubric'.$idx, $values);
        return $output;
    }

    
    public function display_regrade_confirmation($elementname, $changelevel, $value) {
        $html = html_writer::start_tag('div', array('class' => 'gradingform_rubric-regrade', 'role' => 'alert'));
        if ($changelevel<=2) {
            $html .= html_writer::label(get_string('regrademessage1', 'gradingform_rubric'), 'menu' . $elementname . 'regrade');
            $selectoptions = array(
                0 => get_string('regradeoption0', 'gradingform_rubric'),
                1 => get_string('regradeoption1', 'gradingform_rubric')
            );
            $html .= html_writer::select($selectoptions, $elementname.'[regrade]', $value, false);
        } else {
            $html .= get_string('regrademessage5', 'gradingform_rubric');
            $html .= html_writer::empty_tag('input', array('name' => $elementname.'[regrade]', 'value' => 1, 'type' => 'hidden'));
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }

    
    public function display_rubric_mapping_explained($scores) {
        $html = '';
        if (!$scores) {
            return $html;
        }
        if ($scores['minscore'] <> 0) {
            $html .= $this->output->notification(get_string('zerolevelsabsent', 'gradingform_rubric'), 'error');
        }
        $html .= $this->box(
                html_writer::tag('h4', get_string('rubricmapping', 'gradingform_rubric')).
                html_writer::tag('div', get_string('rubricmappingexplained', 'gradingform_rubric', (object)$scores))
                , 'generalbox rubricmappingexplained');
        return $html;
    }
}
