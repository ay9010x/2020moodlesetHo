<?php



defined('MOODLE_INTERNAL') || die();


class gradingform_guide_renderer extends plugin_renderer_base {

    
    public function criterion_template($mode, $options, $elementname = '{NAME}', $criterion = null, $value = null,
                                       $validationerrors = null, $comments = null) {
        global $PAGE;

        if ($criterion === null || !is_array($criterion) || !array_key_exists('id', $criterion)) {
            $criterion = array('id' => '{CRITERION-id}',
                               'description' => '{CRITERION-description}',
                               'sortorder' => '{CRITERION-sortorder}',
                               'class' => '{CRITERION-class}',
                               'descriptionmarkers' => '{CRITERION-descriptionmarkers}',
                               'shortname' => '{CRITERION-shortname}',
                               'maxscore' => '{CRITERION-maxscore}');
        } else {
            foreach (array('sortorder', 'description', 'class', 'shortname', 'descriptionmarkers', 'maxscore') as $key) {
                                if (!array_key_exists($key, $criterion)) {
                    $criterion[$key] = '';
                }
            }
        }

        $criteriontemplate = html_writer::start_tag('tr', array('class' => 'criterion'. $criterion['class'],
            'id' => '{NAME}-criteria-{CRITERION-id}'));
        $descriptionclass = 'description';
        if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FULL) {
            $criteriontemplate .= html_writer::start_tag('td', array('class' => 'controls'));
            foreach (array('moveup', 'delete', 'movedown') as $key) {
                $value = get_string('criterion'.$key, 'gradingform_guide');
                $button = html_writer::empty_tag('input', array('type' => 'submit',
                    'name' => '{NAME}[criteria][{CRITERION-id}]['.$key.']',
                    'id' => '{NAME}-criteria-{CRITERION-id}-'.$key, 'value' => $value, 'title' => $value));
                $criteriontemplate .= html_writer::tag('div', $button, array('class' => $key));
            }
            $criteriontemplate .= html_writer::end_tag('td');             $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]', 'value' => $criterion['sortorder']));

            $shortnameinput = html_writer::empty_tag('input', array('type' => 'text',
                'name' => '{NAME}[criteria][{CRITERION-id}][shortname]',
                'id ' => '{NAME}-criteria-{CRITERION-id}-shortname',
                'value' => $criterion['shortname'],
                'aria-labelledby' => '{NAME}-criterion-name-label'));
            $shortname = html_writer::tag('div', $shortnameinput, array('class' => 'criterionname'));
            $descriptioninput = html_writer::tag('textarea', s($criterion['description']),
                array('name' => '{NAME}[criteria][{CRITERION-id}][description]',
                      'id' => '{NAME}[criteria][{CRITERION-id}][description]', 'cols' => '65', 'rows' => '5'));
            $description = html_writer::tag('div', $descriptioninput, array('class' => 'criteriondesc'));

            $descriptionmarkersinput = html_writer::tag('textarea', s($criterion['descriptionmarkers']),
                array('name' => '{NAME}[criteria][{CRITERION-id}][descriptionmarkers]',
                      'id' => '{NAME}[criteria][{CRITERION-id}][descriptionmarkers]', 'cols' => '65', 'rows' => '5'));
            $descriptionmarkers = html_writer::tag('div', $descriptionmarkersinput, array('class' => 'criteriondescmarkers'));

            $maxscore = html_writer::empty_tag('input', array('type'=> 'text',
                'name' => '{NAME}[criteria][{CRITERION-id}][maxscore]', 'size' => '3',
                'value' => $criterion['maxscore'],
                'id' => '{NAME}[criteria][{CRITERION-id}][maxscore]'));
            $maxscore = html_writer::tag('div', $maxscore, array('class'=>'criterionmaxscore'));
        } else {
            if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FROZEN) {
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][sortorder]', 'value' => $criterion['sortorder']));
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][shortname]', 'value' => $criterion['shortname']));
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][description]', 'value' => $criterion['description']));
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][descriptionmarkers]', 'value' => $criterion['descriptionmarkers']));
                $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => '{NAME}[criteria][{CRITERION-id}][maxscore]', 'value' => $criterion['maxscore']));
            } else if ($mode == gradingform_guide_controller::DISPLAY_EVAL ||
                       $mode == gradingform_guide_controller::DISPLAY_VIEW) {
                $descriptionclass = 'descriptionreadonly';
            }

            $shortnameparams = array(
                'name' => '{NAME}[criteria][{CRITERION-id}][shortname]',
                'id' => '{NAME}[criteria][{CRITERION-id}][shortname]',
                'aria-describedby' => '{NAME}-criterion-name-label'
            );
            $shortname = html_writer::div(s($criterion['shortname']), 'criterionshortname', $shortnameparams);

            $descmarkerclass = '';
            $descstudentclass = '';
            if ($mode == gradingform_guide_controller::DISPLAY_EVAL) {
                if (!get_user_preferences('gradingform_guide-showmarkerdesc', true)) {
                    $descmarkerclass = ' hide';
                }
                if (!get_user_preferences('gradingform_guide-showstudentdesc', true)) {
                    $descstudentclass = ' hide';
                }
            }
            $description = html_writer::tag('div', s($criterion['description']),
                array('class'=>'criteriondescription'.$descstudentclass,
                      'name' => '{NAME}[criteria][{CRITERION-id}][descriptionmarkers]'));
            $descriptionmarkers   = html_writer::tag('div', s($criterion['descriptionmarkers']),
                array('class'=>'criteriondescriptionmarkers'.$descmarkerclass,
                      'name' => '{NAME}[criteria][{CRITERION-id}][descriptionmarkers]'));
            $maxscore   = html_writer::tag('div', s($criterion['maxscore']),
                array('class'=>'criteriondescriptionscore', 'name' => '{NAME}[criteria][{CRITERION-id}][maxscore]'));

                        $description = nl2br($description);
            $descriptionmarkers = nl2br($descriptionmarkers);
        }

        if (isset($criterion['error_description'])) {
            $descriptionclass .= ' error';
        }

        $title = $shortname;
        if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FULL ||
            $mode == gradingform_guide_controller::DISPLAY_PREVIEW) {
            $title .= html_writer::tag('label', get_string('descriptionstudents', 'gradingform_guide'),
                array('for'=>'{NAME}[criteria][{CRITERION-id}][description]'));
            $title .= $description;
            $title .= html_writer::tag('label', get_string('descriptionmarkers', 'gradingform_guide'),
                array('for'=>'{NAME}[criteria][{CRITERION-id}][descriptionmarkers]'));
            $title .= $descriptionmarkers;
            $title .=  html_writer::tag('label', get_string('maxscore', 'gradingform_guide'),
                array('for'=>'{NAME}[criteria][{CRITERION-id}][maxscore]'));
            $title .= $maxscore;
        } else if ($mode == gradingform_guide_controller::DISPLAY_PREVIEW_GRADED ||
                   $mode == gradingform_guide_controller::DISPLAY_VIEW) {
            $title .= $description;
            if (!empty($options['showmarkspercriterionstudents'])) {
                $title .= html_writer::label(get_string('maxscore', 'gradingform_guide'), null);
                $title .= $maxscore;
            }
        } else {
            $title .= $description . $descriptionmarkers;
        }

                $titletdparams = array(
            'class' => $descriptionclass,
            'id' => '{NAME}-criteria-{CRITERION-id}-shortname-cell'
        );

        if ($mode != gradingform_guide_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_guide_controller::DISPLAY_EDIT_FROZEN) {
                        $titletdparams['tabindex'] = '0';
        }

        $criteriontemplate .= html_writer::tag('td', $title, $titletdparams);

        $currentremark = '';
        $currentscore = '';
        if (isset($value['remark'])) {
            $currentremark = $value['remark'];
        }
        if (isset($value['score'])) {
            $currentscore = $value['score'];
        }

                $remarkid = $elementname . '-criteria-' . $criterion['id'] . '-remark';

        if ($mode == gradingform_guide_controller::DISPLAY_EVAL) {
            $scoreclass = '';
            if (!empty($validationerrors[$criterion['id']]['score'])) {
                $scoreclass = 'error';
                $currentscore = $validationerrors[$criterion['id']]['score'];             }

                        $remarkparams = array(
                'name' => '{NAME}[criteria][{CRITERION-id}][remark]',
                'id' => $remarkid,
                'cols' => '65', 'rows' => '5', 'class' => 'markingguideremark',
                'aria-labelledby' => '{NAME}-remarklabel{CRITERION-id}'
            );

                        $input = html_writer::tag('textarea', s($currentremark), $remarkparams);

                        if (!empty($comments)) {
                                $chooserbuttonid = 'criteria-' . $criterion['id'] . '-commentchooser';
                $commentchooserparams = array('id' => $chooserbuttonid, 'class' => 'commentchooser');
                $commentchooser = html_writer::tag('button', get_string('insertcomment', 'gradingform_guide'),
                    $commentchooserparams);

                                $commentoptions = array();
                foreach ($comments as $id => $comment) {
                    $commentoption = new stdClass();
                    $commentoption->id = $id;
                    $commentoption->description = s($comment['description']);
                    $commentoptions[] = $commentoption;
                }

                                $PAGE->requires->string_for_js('insertcomment', 'gradingform_guide');
                                $PAGE->requires->js_call_amd('gradingform_guide/comment_chooser', 'initialise',
                    array($criterion['id'], $chooserbuttonid, $remarkid, $commentoptions));
            }

                        $remarklabelparams = array(
                'class' => 'hidden',
                'id' => '{NAME}-remarklabel{CRITERION-id}'
            );
            $remarklabeltext = get_string('criterionremark', 'gradingform_guide', $criterion['shortname']);
            $remarklabel = html_writer::label($remarklabeltext, $remarkid, false, $remarklabelparams);

            $criteriontemplate .= html_writer::tag('td', $remarklabel . $input . $commentchooser, array('class' => 'remark'));

                        $scoreinputparams = array(
                'type' => 'text',
                'name' => '{NAME}[criteria][{CRITERION-id}][score]',
                'class' => $scoreclass,
                'id' => '{NAME}-criteria-{CRITERION-id}-score',
                'size' => '3',
                'value' => $currentscore,
                'aria-labelledby' => '{NAME}-score-label'
            );
            $score = html_writer::empty_tag('input', $scoreinputparams);
            $score .= html_writer::div('/' . s($criterion['maxscore']));

            $criteriontemplate .= html_writer::tag('td', $score, array('class' => 'score'));
        } else if ($mode == gradingform_guide_controller::DISPLAY_EVAL_FROZEN) {
            $criteriontemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => '{NAME}[criteria][{CRITERION-id}][remark]', 'value' => $currentremark));
        } else if ($mode == gradingform_guide_controller::DISPLAY_REVIEW ||
            $mode == gradingform_guide_controller::DISPLAY_VIEW) {

                        $remarkdescparams = array(
                'id' => '{NAME}-criteria-{CRITERION-id}-remark-desc'
            );
            $remarkdesctext = get_string('criterionremark', 'gradingform_guide', $criterion['shortname']);
            $remarkdesc = html_writer::div($remarkdesctext, 'hidden', $remarkdescparams);

                        $remarkdiv = html_writer::div(s($currentremark));
            $remarkcellparams = array(
                'class' => 'remark',
                'tabindex' => '0',
                'id' => '{NAME}-criteria-{CRITERION-id}-remark',
                'aria-describedby' => '{NAME}-criteria-{CRITERION-id}-remark-desc'
            );
            $criteriontemplate .= html_writer::tag('td', $remarkdesc . $remarkdiv, $remarkcellparams);

                        if (!empty($options['showmarkspercriterionstudents'])) {
                $scorecellparams = array(
                    'class' => 'score',
                    'tabindex' => '0',
                    'id' => '{NAME}-criteria-{CRITERION-id}-score',
                    'aria-describedby' => '{NAME}-score-label'
                );
                $scorediv = html_writer::div(s($currentscore) . ' / ' . s($criterion['maxscore']));
                $criteriontemplate .= html_writer::tag('td', $scorediv, $scorecellparams);
            }
        }
        $criteriontemplate .= html_writer::end_tag('tr'); 
        $criteriontemplate = str_replace('{NAME}', $elementname, $criteriontemplate);
        $criteriontemplate = str_replace('{CRITERION-id}', $criterion['id'], $criteriontemplate);
        return $criteriontemplate;
    }

    
    public function comment_template($mode, $elementname = '{NAME}', $comment = null) {
        if ($comment === null || !is_array($comment) || !array_key_exists('id', $comment)) {
            $comment = array('id' => '{COMMENT-id}',
                'description' => '{COMMENT-description}',
                'sortorder' => '{COMMENT-sortorder}',
                'class' => '{COMMENT-class}');
        } else {
            foreach (array('sortorder', 'description', 'class') as $key) {
                                if (!array_key_exists($key, $comment)) {
                    $criterion[$key] = '';
                }
            }
        }
        $commenttemplate = html_writer::start_tag('tr', array('class' => 'criterion'. $comment['class'],
            'id' => '{NAME}-comments-{COMMENT-id}'));
        if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FULL) {
            $commenttemplate .= html_writer::start_tag('td', array('class' => 'controls'));
            foreach (array('moveup', 'delete', 'movedown') as $key) {
                $value = get_string('comments'.$key, 'gradingform_guide');
                $button = html_writer::empty_tag('input', array('type' => 'submit',
                    'name' => '{NAME}[comments][{COMMENT-id}]['.$key.']', 'id' => '{NAME}-comments-{COMMENT-id}-'.$key,
                    'value' => $value, 'title' => $value));
                $commenttemplate .= html_writer::tag('div', $button, array('class' => $key));
            }
            $commenttemplate .= html_writer::end_tag('td');             $commenttemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => '{NAME}[comments][{COMMENT-id}][sortorder]', 'value' => $comment['sortorder']));
            $description = html_writer::tag('textarea', s($comment['description']),
                array('name' => '{NAME}[comments][{COMMENT-id}][description]',
                      'id' => '{NAME}-comments-{COMMENT-id}-description',
                      'aria-labelledby' => '{NAME}-comment-label', 'cols' => '65', 'rows' => '5'));
            $description = html_writer::tag('div', $description, array('class'=>'criteriondesc'));
        } else {
            if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FROZEN) {
                $commenttemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => '{NAME}[comments][{COMMENT-id}][sortorder]', 'value' => $comment['sortorder']));
                $commenttemplate .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => '{NAME}[comments][{COMMENT-id}][description]', 'value' => $comment['description']));
            }
            if ($mode == gradingform_guide_controller::DISPLAY_EVAL) {
                $description = html_writer::tag('span', s($comment['description']),
                    array('name' => '{NAME}[comments][{COMMENT-id}][description]',
                          'title' => get_string('clicktocopy', 'gradingform_guide'),
                          'id' => '{NAME}[comments][{COMMENT-id}]', 'class'=>'markingguidecomment'));
            } else {
                $description = s($comment['description']);
            }
                        $description = nl2br($description);
        }
        $descriptionclass = 'description';
        if (isset($comment['error_description'])) {
            $descriptionclass .= ' error';
        }
        $descriptioncellparams = array(
            'class' => $descriptionclass,
            'id' => '{NAME}-comments-{COMMENT-id}-description-cell'
        );
                if ($mode != gradingform_guide_controller::DISPLAY_EDIT_FULL &&
            $mode != gradingform_guide_controller::DISPLAY_EDIT_FROZEN) {
            $descriptioncellparams['tabindex'] = '0';
        }
        $commenttemplate .= html_writer::tag('td', $description, $descriptioncellparams);
        $commenttemplate .= html_writer::end_tag('tr'); 
        $commenttemplate = str_replace('{NAME}', $elementname, $commenttemplate);
        $commenttemplate = str_replace('{COMMENT-id}', $comment['id'], $commenttemplate);
        return $commenttemplate;
    }
    
    protected function guide_template($mode, $options, $elementname, $criteriastr, $commentstr) {
        $classsuffix = '';         switch ($mode) {
            case gradingform_guide_controller::DISPLAY_EDIT_FULL:
                $classsuffix = ' editor editable';
                break;
            case gradingform_guide_controller::DISPLAY_EDIT_FROZEN:
                $classsuffix = ' editor frozen';
                break;
            case gradingform_guide_controller::DISPLAY_PREVIEW:
            case gradingform_guide_controller::DISPLAY_PREVIEW_GRADED:
                $classsuffix = ' editor preview';
                break;
            case gradingform_guide_controller::DISPLAY_EVAL:
                $classsuffix = ' evaluate editable';
                break;
            case gradingform_guide_controller::DISPLAY_EVAL_FROZEN:
                $classsuffix = ' evaluate frozen';
                break;
            case gradingform_guide_controller::DISPLAY_REVIEW:
                $classsuffix = ' review';
                break;
            case gradingform_guide_controller::DISPLAY_VIEW:
                $classsuffix = ' view';
                break;
        }

        $guidetemplate = html_writer::start_tag('div', array('id' => 'guide-{NAME}',
            'class' => 'clearfix gradingform_guide'.$classsuffix));

                $guidedescparams = array(
            'id' => 'guide-{NAME}-desc',
            'aria-hidden' => 'true'
        );
        $guidetemplate .= html_writer::div(get_string('guide', 'gradingform_guide'), 'hidden', $guidedescparams);

                $guidetemplate .= html_writer::div(get_string('criterionname', 'gradingform_guide'), 'hidden',
            array('id' => '{NAME}-criterion-name-label'));

                $guidetemplate .= html_writer::div(get_string('score', 'gradingform_guide'), 'hidden', array('id' => '{NAME}-score-label'));

                $criteriatableparams = array(
            'class' => 'criteria',
            'id' => '{NAME}-criteria',
            'aria-describedby' => 'guide-{NAME}-desc');
        $guidetemplate .= html_writer::tag('table', $criteriastr, $criteriatableparams);
        if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('addcriterion', 'gradingform_guide');
            $input = html_writer::empty_tag('input', array('type' => 'submit', 'name' => '{NAME}[criteria][addcriterion]',
                'id' => '{NAME}-criteria-addcriterion', 'value' => $value, 'title' => $value));
            $guidetemplate .= html_writer::tag('div', $input, array('class' => 'addcriterion'));
        }

        if (!empty($commentstr)) {
            $guidetemplate .= html_writer::div(get_string('comments', 'gradingform_guide'), 'commentheader',
                array('id' => '{NAME}-comments-label'));
            $guidetemplate .= html_writer::div(get_string('comment', 'gradingform_guide'), 'hidden',
                array('id' => '{NAME}-comment-label', 'aria-hidden' => 'true'));
            $commentstableparams = array(
                'class' => 'comments',
                'id' => '{NAME}-comments',
                'aria-describedby' => '{NAME}-comments-label');
            $guidetemplate .= html_writer::tag('table', $commentstr, $commentstableparams);
        }
        if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FULL) {
            $value = get_string('addcomment', 'gradingform_guide');
            $input = html_writer::empty_tag('input', array('type' => 'submit', 'name' => '{NAME}[comments][addcomment]',
                'id' => '{NAME}-comments-addcomment', 'value' => $value, 'title' => $value));
            $guidetemplate .= html_writer::tag('div', $input, array('class' => 'addcomment'));
        }

        $guidetemplate .= $this->guide_edit_options($mode, $options);
        $guidetemplate .= html_writer::end_tag('div');

        return str_replace('{NAME}', $elementname, $guidetemplate);
    }

    
    protected function guide_edit_options($mode, $options) {
        if ($mode != gradingform_guide_controller::DISPLAY_EDIT_FULL
            && $mode != gradingform_guide_controller::DISPLAY_EDIT_FROZEN
            && $mode != gradingform_guide_controller::DISPLAY_PREVIEW) {
                        return;
        }
        $html = html_writer::start_tag('div', array('class' => 'options'));
        $html .= html_writer::tag('div', get_string('guideoptions', 'gradingform_guide'), array('class' => 'optionsheading'));
        $attrs = array('type' => 'hidden', 'name' => '{NAME}[options][optionsset]', 'value' => 1);
        $html .= html_writer::empty_tag('input', $attrs);
        foreach ($options as $option => $value) {
            $html .= html_writer::start_tag('div', array('class' => 'option '.$option));
            $attrs = array('name' => '{NAME}[options]['.$option.']', 'id' => '{NAME}-options-'.$option);
            switch ($option) {
                case 'sortlevelsasc':
                                        $html .= html_writer::tag('span', get_string($option, 'gradingform_guide'), array('class' => 'label'));
                    $value = (int)(!!$value);                     if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FULL) {
                        $selectoptions = array(0 => get_string($option.'0', 'gradingform_guide'),
                            1 => get_string($option.'1', 'gradingform_guide'));
                        $valuestr = html_writer::select($selectoptions, $attrs['name'], $value, false, array('id' => $attrs['id']));
                        $html .= html_writer::tag('span', $valuestr, array('class' => 'value'));
                                            } else {
                        $html .= html_writer::tag('span', get_string($option.$value, 'gradingform_guide'),
                            array('class' => 'value'));
                        if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FROZEN) {
                            $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                        }
                    }
                    break;
                default:
                    if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FROZEN && $value) {
                        $html .= html_writer::empty_tag('input', $attrs + array('type' => 'hidden', 'value' => $value));
                    }
                                        $attrs['type'] = 'checkbox';
                    $attrs['value'] = 1;
                    if ($value) {
                        $attrs['checked'] = 'checked';
                    }
                    if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FROZEN ||
                        $mode == gradingform_guide_controller::DISPLAY_PREVIEW) {
                        $attrs['disabled'] = 'disabled';
                        unset($attrs['name']);
                    }
                    $html .= html_writer::empty_tag('input', $attrs);
                    $html .= html_writer::tag('label', get_string($option, 'gradingform_guide'), array('for' => $attrs['id']));
                    break;
            }
            $html .= html_writer::end_tag('div');         }
        $html .= html_writer::end_tag('div');         return $html;
    }

    
    public function display_guide($criteria, $comments, $options, $mode, $elementname = null, $values = null,
                                  $validationerrors = null) {
        $criteriastr = '';
        $cnt = 0;
        foreach ($criteria as $id => $criterion) {
            $criterion['class'] = $this->get_css_class_suffix($cnt++, count($criteria) -1);
            $criterion['id'] = $id;
            if (isset($values['criteria'][$id])) {
                $criterionvalue = $values['criteria'][$id];
            } else {
                $criterionvalue = null;
            }
            $criteriastr .= $this->criterion_template($mode, $options, $elementname, $criterion, $criterionvalue,
                                                      $validationerrors, $comments);
        }

        $cnt = 0;
        $commentstr = '';
                if ($mode == gradingform_guide_controller::DISPLAY_EDIT_FULL ||
            $mode == gradingform_guide_controller::DISPLAY_EDIT_FROZEN ||
            $mode == gradingform_guide_controller::DISPLAY_PREVIEW ||
            $mode == gradingform_guide_controller::DISPLAY_EVAL_FROZEN) {

            foreach ($comments as $id => $comment) {
                $comment['id'] = $id;
                $comment['class'] = $this->get_css_class_suffix($cnt++, count($comments) -1);
                $commentstr .= $this->comment_template($mode, $elementname, $comment);
            }
        }
        $output = $this->guide_template($mode, $options, $elementname, $criteriastr, $commentstr);
        if ($mode == gradingform_guide_controller::DISPLAY_EVAL) {
            $showdesc = get_user_preferences('gradingform_guide-showmarkerdesc', true);
            $showdescstud = get_user_preferences('gradingform_guide-showstudentdesc', true);
            $checked1 = array();
            $checked2 = array();
            $checked_s1 = array();
            $checked_s2 = array();
            $checked = array('checked' => 'checked');
            if ($showdesc) {
                $checked1 = $checked;
            } else {
                $checked2 = $checked;
            }
            if ($showdescstud) {
                $checked_s1 = $checked;
            } else {
                $checked_s2 = $checked;
            }

            $radio1 = html_writer::tag('input', get_string('showmarkerdesc', 'gradingform_guide'), array('type' => 'radio',
                'name' => 'showmarkerdesc',
                'value' => "true")+$checked1);
            $radio1 = html_writer::tag('label', $radio1);
            $radio2 = html_writer::tag('input', get_string('hidemarkerdesc', 'gradingform_guide'), array('type' => 'radio',
                'name' => 'showmarkerdesc',
                'value' => "false")+$checked2);
            $radio2 = html_writer::tag('label', $radio2);
            $output .= html_writer::tag('div', $radio1 . $radio2, array('class' => 'showmarkerdesc'));

            $radio1 = html_writer::tag('input', get_string('showstudentdesc', 'gradingform_guide'), array('type' => 'radio',
                'name' => 'showstudentdesc',
                'value' => "true")+$checked_s1);
            $radio1 = html_writer::tag('label', $radio1);
            $radio2 = html_writer::tag('input', get_string('hidestudentdesc', 'gradingform_guide'), array('type' => 'radio',
                'name' => 'showstudentdesc',
                'value' => "false")+$checked_s2);
            $radio2 = html_writer::tag('label', $radio2);
            $output .= html_writer::tag('div', $radio1 . $radio2, array('class' => 'showstudentdesc'));
        }
        return $output;
    }

    
    protected function get_css_class_suffix($idx, $maxidx) {
        $class = '';
        if ($idx == 0) {
            $class .= ' first';
        }
        if ($idx == $maxidx) {
            $class .= ' last';
        }
        if ($idx % 2) {
            $class .= ' odd';
        } else {
            $class .= ' even';
        }
        return $class;
    }

    
    public function display_instances($instances, $defaultcontent, $cangrade) {
        $return = '';
        if (count($instances)) {
            $return .= html_writer::start_tag('div', array('class' => 'advancedgrade'));
            $idx = 0;
            foreach ($instances as $instance) {
                $return .= $this->display_instance($instance, $idx++, $cangrade);
            }
            $return .= html_writer::end_tag('div');
        }
        return $return. $defaultcontent;
    }

    
    public function display_instance(gradingform_guide_instance $instance, $idx, $cangrade) {
        $criteria = $instance->get_controller()->get_definition()->guide_criteria;
        $options = $instance->get_controller()->get_options();
        $values = $instance->get_guide_filling();
        if ($cangrade) {
            $mode = gradingform_guide_controller::DISPLAY_REVIEW;
        } else {
            $mode = gradingform_guide_controller::DISPLAY_VIEW;
        }

        $output = $this->box($instance->get_controller()->get_formatted_description(), 'gradingform_guide-description').
                  $this->display_guide($criteria, array(), $options, $mode, 'guide'.$idx, $values);
        return $output;
    }


    
    public function display_regrade_confirmation($elementname, $changelevel, $value) {
        $html = html_writer::start_tag('div', array('class' => 'gradingform_guide-regrade', 'role' => 'alert'));
        if ($changelevel<=2) {
            $html .= get_string('regrademessage1', 'gradingform_guide');
            $selectoptions = array(
                0 => get_string('regradeoption0', 'gradingform_guide'),
                1 => get_string('regradeoption1', 'gradingform_guide')
            );
            $html .= html_writer::select($selectoptions, $elementname.'[regrade]', $value, false);
        } else {
            $html .= get_string('regrademessage5', 'gradingform_guide');
            $html .= html_writer::empty_tag('input', array('name' => $elementname.'[regrade]', 'value' => 1, 'type' => 'hidden'));
        }
        $html .= html_writer::end_tag('div');
        return $html;
    }
    
    public function display_guide_mapping_explained($scores) {
        $html = '';
        if (!$scores) {
            return $html;
        }
        if (isset($scores['modulegrade']) && $scores['maxscore'] != $scores['modulegrade']) {
            $html .= $this->box(html_writer::tag('div', get_string('guidemappingexplained', 'gradingform_guide', (object)$scores))
                , 'generalbox gradingform_guide-error');
        }

        return $html;
    }
}
