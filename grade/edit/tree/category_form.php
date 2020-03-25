<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';

class edit_category_form extends moodleform {
    private $aggregation_options = array();

    function definition() {
        global $CFG, $COURSE, $DB, $OUTPUT;
        $mform =& $this->_form;

        $category = $this->_customdata['current'];

        $this->aggregation_options = grade_helper::get_aggregation_strings();

                $mform->addElement('header', 'headercategory', get_string('gradecategory', 'grades'));
        $mform->addElement('text', 'fullname', get_string('categoryname', 'grades'));
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', null, 'required', null, 'client');

        $mform->addElement('select', 'aggregation', get_string('aggregation', 'grades'), $this->aggregation_options);
        $mform->addHelpButton('aggregation', 'aggregation', 'grades');

        if ((int)$CFG->grade_aggregation_flag & 2) {
            $mform->setAdvanced('aggregation');
        }

        $mform->addElement('checkbox', 'aggregateonlygraded', get_string('aggregateonlygraded', 'grades'));
        $mform->addHelpButton('aggregateonlygraded', 'aggregateonlygraded', 'grades');

        if ((int)$CFG->grade_aggregateonlygraded_flag & 2) {
            $mform->setAdvanced('aggregateonlygraded');
        }

        if (empty($CFG->enableoutcomes)) {
            $mform->addElement('hidden', 'aggregateoutcomes');
            $mform->setType('aggregateoutcomes', PARAM_INT);
        } else {
            $mform->addElement('checkbox', 'aggregateoutcomes', get_string('aggregateoutcomes', 'grades'));
            $mform->addHelpButton('aggregateoutcomes', 'aggregateoutcomes', 'grades');
            if ((int)$CFG->grade_aggregateoutcomes_flag & 2) {
                $mform->setAdvanced('aggregateoutcomes');
            }
        }

        $mform->addElement('text', 'keephigh', get_string('keephigh', 'grades'), 'size="3"');
        $mform->setType('keephigh', PARAM_INT);
        $mform->addHelpButton('keephigh', 'keephigh', 'grades');
        if ((int)$CFG->grade_keephigh_flag & 2) {
            $mform->setAdvanced('keephigh');
        }

        $mform->addElement('text', 'droplow', get_string('droplow', 'grades'), 'size="3"');
        $mform->setType('droplow', PARAM_INT);
        $mform->addHelpButton('droplow', 'droplow', 'grades');
        $mform->disabledIf('droplow', 'keephigh', 'noteq', 0);
        if ((int)$CFG->grade_droplow_flag & 2) {
            $mform->setAdvanced('droplow');
        }

        $mform->disabledIf('keephigh', 'droplow', 'noteq', 0);
        $mform->disabledIf('droplow', 'keephigh', 'noteq', 0);

                        $mform->addElement('header', 'general', get_string('categorytotal', 'grades'));

        $mform->addElement('text', 'grade_item_itemname', get_string('categorytotalname', 'grades'));
        $mform->setType('grade_item_itemname', PARAM_TEXT);
        $mform->setAdvanced('grade_item_itemname');

        $mform->addElement('text', 'grade_item_iteminfo', get_string('iteminfo', 'grades'));
        $mform->addHelpButton('grade_item_iteminfo', 'iteminfo', 'grades');
        $mform->setType('grade_item_iteminfo', PARAM_TEXT);

        $mform->addElement('text', 'grade_item_idnumber', get_string('idnumbermod'));
        $mform->addHelpButton('grade_item_idnumber', 'idnumbermod');
        $mform->setType('grade_item_idnumber', PARAM_RAW);

        if (!empty($category->id)) {
            $gradecategory = grade_category::fetch(array('id' => $category->id));
            $gradeitem = $gradecategory->load_grade_item();

                                    if ($gradeitem->has_overridden_grades()) {
                                if ($gradeitem->gradetype == GRADE_TYPE_SCALE) {
                    $gradesexistmsg = get_string('modgradecategorycantchangegradetyporscalemsg', 'grades');
                } else {
                    $gradesexistmsg = get_string('modgradecategorycantchangegradetypemsg', 'grades');
                }
                $notification = new \core\output\notification($gradesexistmsg, \core\output\notification::NOTIFY_INFO);
                $notification->set_show_closebutton(false);
                $mform->addElement('static', 'gradesexistmsg', '', $OUTPUT->render($notification));
            }
        }

        $options = array(GRADE_TYPE_NONE=>get_string('typenone', 'grades'),
                         GRADE_TYPE_VALUE=>get_string('typevalue', 'grades'),
                         GRADE_TYPE_SCALE=>get_string('typescale', 'grades'),
                         GRADE_TYPE_TEXT=>get_string('typetext', 'grades'));

        $mform->addElement('select', 'grade_item_gradetype', get_string('gradetype', 'grades'), $options);
        $mform->addHelpButton('grade_item_gradetype', 'gradetype', 'grades');
        $mform->setDefault('grade_item_gradetype', GRADE_TYPE_VALUE);
        $mform->disabledIf('grade_item_gradetype', 'aggregation', 'eq', GRADE_AGGREGATE_SUM);

                        
        $options = array(0=>get_string('usenoscale', 'grades'));
        if ($scales = grade_scale::fetch_all_local($COURSE->id)) {
            foreach ($scales as $scale) {
                $options[$scale->id] = $scale->get_name();
            }
        }
        if ($scales = grade_scale::fetch_all_global()) {
            foreach ($scales as $scale) {
                $options[$scale->id] = $scale->get_name();
            }
        }
                if (!empty($category->grade_item_scaleid) and !isset($options[$category->grade_item_scaleid])) {
            if ($scale = grade_scale::fetch(array('id'=>$category->grade_item_scaleid))) {
                $options[$scale->id] = $scale->get_name().' '.get_string('incorrectcustomscale', 'grades');
            }
        }
        $mform->addElement('select', 'grade_item_scaleid', get_string('scale'), $options);
        $mform->addHelpButton('grade_item_scaleid', 'typescale', 'grades');
        $mform->disabledIf('grade_item_scaleid', 'grade_item_gradetype', 'noteq', GRADE_TYPE_SCALE);
        $mform->disabledIf('grade_item_scaleid', 'aggregation', 'eq', GRADE_AGGREGATE_SUM);

        $choices = array();
        $choices[''] = get_string('choose');
        $choices['no'] = get_string('no');
        $choices['yes'] = get_string('yes');
        $mform->addElement('select', 'grade_item_rescalegrades', get_string('modgradecategoryrescalegrades', 'grades'), $choices);
        $mform->addHelpButton('grade_item_rescalegrades', 'modgradecategoryrescalegrades', 'grades');
        $mform->disabledIf('grade_item_rescalegrades', 'grade_item_gradetype', 'noteq', GRADE_TYPE_VALUE);

        $mform->addElement('text', 'grade_item_grademax', get_string('grademax', 'grades'));
        $mform->setType('grade_item_grademax', PARAM_RAW);
        $mform->addHelpButton('grade_item_grademax', 'grademax', 'grades');
        $mform->disabledIf('grade_item_grademax', 'grade_item_gradetype', 'noteq', GRADE_TYPE_VALUE);
        $mform->disabledIf('grade_item_grademax', 'aggregation', 'eq', GRADE_AGGREGATE_SUM);

        if ((bool) get_config('moodle', 'grade_report_showmin')) {
            $mform->addElement('text', 'grade_item_grademin', get_string('grademin', 'grades'));
            $mform->setType('grade_item_grademin', PARAM_RAW);
            $mform->addHelpButton('grade_item_grademin', 'grademin', 'grades');
            $mform->disabledIf('grade_item_grademin', 'grade_item_gradetype', 'noteq', GRADE_TYPE_VALUE);
            $mform->disabledIf('grade_item_grademin', 'aggregation', 'eq', GRADE_AGGREGATE_SUM);
        }

        $mform->addElement('text', 'grade_item_gradepass', get_string('gradepass', 'grades'));
        $mform->setType('grade_item_gradepass', PARAM_RAW);
        $mform->addHelpButton('grade_item_gradepass', 'gradepass', 'grades');
        $mform->disabledIf('grade_item_gradepass', 'grade_item_gradetype', 'eq', GRADE_TYPE_NONE);
        $mform->disabledIf('grade_item_gradepass', 'grade_item_gradetype', 'eq', GRADE_TYPE_TEXT);

                $default_gradedisplaytype = grade_get_setting($COURSE->id, 'displaytype', $CFG->grade_displaytype);
        $options = array(GRADE_DISPLAY_TYPE_DEFAULT            => get_string('default', 'grades'),
                         GRADE_DISPLAY_TYPE_REAL               => get_string('real', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE         => get_string('percentage', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER             => get_string('letter', 'grades'),
                         GRADE_DISPLAY_TYPE_REAL_PERCENTAGE    => get_string('realpercentage', 'grades'),
                         GRADE_DISPLAY_TYPE_REAL_LETTER        => get_string('realletter', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER_REAL        => get_string('letterreal', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE  => get_string('letterpercentage', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER  => get_string('percentageletter', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE_REAL    => get_string('percentagereal', 'grades')
                         );

        asort($options);

        foreach ($options as $key=>$option) {
            if ($key == $default_gradedisplaytype) {
                $options[GRADE_DISPLAY_TYPE_DEFAULT] = get_string('defaultprev', 'grades', $option);
                break;
            }
        }
        $mform->addElement('select', 'grade_item_display', get_string('gradedisplaytype', 'grades'), $options);
        $mform->addHelpButton('grade_item_display', 'gradedisplaytype', 'grades');

        $default_gradedecimals = grade_get_setting($COURSE->id, 'decimalpoints', $CFG->grade_decimalpoints);
        $options = array(-1=>get_string('defaultprev', 'grades', $default_gradedecimals), 0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
        $mform->addElement('select', 'grade_item_decimals', get_string('decimalpoints', 'grades'), $options);
        $mform->addHelpButton('grade_item_decimals', 'decimalpoints', 'grades');
        $mform->setDefault('grade_item_decimals', -1);
        $mform->disabledIf('grade_item_decimals', 'grade_item_display', 'eq', GRADE_DISPLAY_TYPE_LETTER);

        if ($default_gradedisplaytype == GRADE_DISPLAY_TYPE_LETTER) {
            $mform->disabledIf('grade_item_decimals', 'grade_item_display', "eq", GRADE_DISPLAY_TYPE_DEFAULT);
        }

                        $mform->addElement('checkbox', 'grade_item_hidden', get_string('hidden', 'grades'));
        $mform->addHelpButton('grade_item_hidden', 'hidden', 'grades');
        $mform->addElement('date_time_selector', 'grade_item_hiddenuntil', get_string('hiddenuntil', 'grades'), array('optional'=>true));
        $mform->disabledIf('grade_item_hidden', 'grade_item_hiddenuntil[off]', 'notchecked');

                $mform->addElement('checkbox', 'grade_item_locked', get_string('locked', 'grades'));
        $mform->addHelpButton('grade_item_locked', 'locked', 'grades');

        $mform->addElement('date_time_selector', 'grade_item_locktime', get_string('locktime', 'grades'), array('optional'=>true));
        $mform->disabledIf('grade_item_locktime', 'grade_item_gradetype', 'eq', GRADE_TYPE_NONE);

        $mform->addElement('header', 'headerparent', get_string('parentcategory', 'grades'));

        $mform->addElement('advcheckbox', 'grade_item_weightoverride', get_string('adjustedweight', 'grades'));
        $mform->addHelpButton('grade_item_weightoverride', 'weightoverride', 'grades');

        $mform->addElement('text', 'grade_item_aggregationcoef2', get_string('weight', 'grades'));
        $mform->addHelpButton('grade_item_aggregationcoef2', 'weight', 'grades');
        $mform->setType('grade_item_aggregationcoef2', PARAM_RAW);
        $mform->disabledIf('grade_item_aggregationcoef2', 'grade_item_weightoverride');

        $options = array();
        $default = -1;
        $categories = grade_category::fetch_all(array('courseid'=>$COURSE->id));

        foreach ($categories as $cat) {
            $cat->apply_forced_settings();
            $options[$cat->id] = $cat->get_name();
            if ($cat->is_course_category()) {
                $default = $cat->id;
            }
        }

        if (count($categories) > 1) {
            $mform->addElement('select', 'parentcategory', get_string('parentcategory', 'grades'), $options);
            $mform->setDefault('parentcategory', $default);
            $mform->addElement('static', 'currentparentaggregation', get_string('currentparentaggregation', 'grades'));
        }

                $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', 0);
        $mform->setType('courseid', PARAM_INT);

        $gpr = $this->_customdata['gpr'];
        $gpr->add_mform_elements($mform);

        if (isset($CFG->grade_item_advanced)) {
            $advanced = explode(',', $CFG->grade_item_advanced);
            foreach ($advanced as $el) {
                $el = 'grade_item_'.$el;
                if ($mform->elementExists($el)) {
                    $mform->setAdvanced($el);
                }
            }
        }

                $this->add_action_buttons();
        $this->set_data($category);
    }


    function definition_after_data() {
        global $CFG, $COURSE;

        $mform =& $this->_form;

        $somecat = new grade_category();

        foreach ($somecat->forceable as $property) {
            if ((int)$CFG->{"grade_{$property}_flag"} & 1) {
                if ($mform->elementExists($property)) {
                    if (empty($CFG->grade_hideforcedsettings)) {
                        $mform->hardFreeze($property);
                    } else {
                        if ($mform->elementExists($property)) {
                            $mform->removeElement($property);
                        }
                    }
                }
            }
        }

        if ($CFG->grade_droplow > 0) {
            if ($mform->elementExists('keephigh')) {
                $mform->removeElement('keephigh');
            }
        } else if ($CFG->grade_keephigh > 0) {
            if ($mform->elementExists('droplow')) {
                $mform->removeElement('droplow');
            }
        }

        if ($id = $mform->getElementValue('id')) {
            $grade_category = grade_category::fetch(array('id'=>$id));
            $grade_item = $grade_category->load_grade_item();

                        if ($grade_category->is_course_category()) {
                if ($mform->elementExists('parentcategory')) {
                    $mform->removeElement('parentcategory');
                }
                if ($mform->elementExists('currentparentaggregation')) {
                    $mform->removeElement('currentparentaggregation');
                }

            } else {
                                if ($mform->elementExists('parentcategory')) {
                    $mform->hardFreeze('parentcategory');
                }
                $parent_cat = $grade_category->get_parent_category();
                $mform->setDefault('currentparentaggregation', $this->aggregation_options[$parent_cat->aggregation]);

            }

                        if (!$grade_category->can_apply_limit_rules()) {
                if ($mform->elementExists('keephigh')) {
                    $mform->setConstant('keephigh', 0);
                    $mform->hardFreeze('keephigh');
                }
                if ($mform->elementExists('droplow')) {
                    $mform->setConstant('droplow', 0);
                    $mform->hardFreeze('droplow');
                }
            }

            if ($grade_item->is_calculated()) {
                                if ($mform->elementExists('aggregation')) {
                    $mform->removeElement('aggregation');
                }
                if ($mform->elementExists('keephigh')) {
                    $mform->removeElement('keephigh');
                }
                if ($mform->elementExists('droplow')) {
                    $mform->removeElement('droplow');
                }
                if ($mform->elementExists('aggregateonlygraded')) {
                    $mform->removeElement('aggregateonlygraded');
                }
                if ($mform->elementExists('aggregateoutcomes')) {
                    $mform->removeElement('aggregateoutcomes');
                }
            }

                        if ($grade_category->is_course_category()) {
                unset($mform->_rules['fullname']);
                $key = array_search('fullname', $mform->_required);
                unset($mform->_required[$key]);
            }

                        if ($grade_category->is_course_category() && $mform->getElementValue('fullname') == '?') {
                $mform->setDefault('fullname', '');
            }
                        if ($mform->elementExists('aggregation')) {
                $allaggoptions = array_keys($this->aggregation_options);
                $agg_el =& $mform->getElement('aggregation');
                $visible = explode(',', $CFG->grade_aggregations_visible);
                if (!is_null($grade_category->aggregation)) {
                                        $visible[] = $grade_category->aggregation;
                }
                foreach ($allaggoptions as $type) {
                    if (!in_array($type, $visible)) {
                        $agg_el->removeOption($type);
                    }
                }
            }

        } else {
                        if ($mform->elementExists('currentparentaggregation')) {
                $mform->removeElement('currentparentaggregation');
            }
                        if ($mform->elementExists('aggregation')) {
                $allaggoptions = array_keys($this->aggregation_options);
                $agg_el =& $mform->getElement('aggregation');
                $visible = explode(',', $CFG->grade_aggregations_visible);
                foreach ($allaggoptions as $type) {
                    if (!in_array($type, $visible)) {
                        $agg_el->removeOption($type);
                    }
                }
            }

            $mform->removeElement('grade_item_rescalegrades');
        }


                if (!$mform->elementExists('parentcategory')) {
            $mform->removeElement('headerparent');
        }

        if ($id = $mform->getElementValue('id')) {
            $grade_category = grade_category::fetch(array('id'=>$id));
            $grade_item = $grade_category->load_grade_item();

            $mform->setDefault('grade_item_hidden', (int) $grade_item->hidden);

            if ($grade_item->is_outcome_item()) {
                                $mform->removeElement('grade_item_grademax');
                if ($mform->elementExists('grade_item_grademin')) {
                    $mform->removeElement('grade_item_grademin');
                }
                $mform->removeElement('grade_item_gradetype');
                $mform->removeElement('grade_item_display');
                $mform->removeElement('grade_item_decimals');
                $mform->hardFreeze('grade_item_scaleid');
                        } else if ($grade_item->has_overridden_grades()) {
                                $mform->hardFreeze('grade_item_gradetype, grade_item_scaleid');

                                if ($grade_item->gradetype == GRADE_TYPE_SCALE) {
                    $mform->removeElement('grade_item_rescalegrades');
                    $mform->removeElement('grade_item_grademax');
                    if ($mform->elementExists('grade_item_grademin')) {
                        $mform->removeElement('grade_item_grademin');
                    }
                } else {                     $mform->removeElement('grade_item_scaleid');
                    $mform->disabledIf('grade_item_grademax', 'grade_item_rescalegrades', 'eq', '');
                    $mform->disabledIf('grade_item_grademin', 'grade_item_rescalegrades', 'eq', '');
                }
            } else {                 $mform->removeElement('grade_item_rescalegrades');
            }

                        if ($grade_item->is_course_item()) {
                if ($mform->elementExists('grade_item_aggregationcoef')) {
                    $mform->removeElement('grade_item_aggregationcoef');
                }

                if ($mform->elementExists('grade_item_weightoverride')) {
                    $mform->removeElement('grade_item_weightoverride');
                }
                if ($mform->elementExists('grade_item_aggregationcoef2')) {
                    $mform->removeElement('grade_item_aggregationcoef2');
                }
            } else {
                if ($grade_item->is_category_item()) {
                    $category = $grade_item->get_item_category();
                    $parent_category = $category->get_parent_category();
                } else {
                    $parent_category = $grade_item->get_parent_category();
                }

                $parent_category->apply_forced_settings();

                if (!$parent_category->is_aggregationcoef_used()) {
                    if ($mform->elementExists('grade_item_aggregationcoef')) {
                        $mform->removeElement('grade_item_aggregationcoef');
                    }
                } else {

                    $coefstring = $grade_item->get_coefstring();

                    if ($coefstring == 'aggregationcoefextrasum' || $coefstring == 'aggregationcoefextraweightsum') {
                                                $coefstring = 'aggregationcoefextrasum';
                        $element =& $mform->createElement('checkbox', 'grade_item_aggregationcoef', get_string($coefstring, 'grades'));
                    } else {
                        $element =& $mform->createElement('text', 'grade_item_aggregationcoef', get_string($coefstring, 'grades'));
                    }
                    $mform->insertElementBefore($element, 'parentcategory');
                    $mform->addHelpButton('grade_item_aggregationcoef', $coefstring, 'grades');
                }

                                                if ($parent_category->aggregation != GRADE_AGGREGATE_SUM
                        || (empty($CFG->grade_includescalesinaggregation) && $grade_item->gradetype == GRADE_TYPE_SCALE)) {
                    if ($mform->elementExists('grade_item_weightoverride')) {
                        $mform->removeElement('grade_item_weightoverride');
                    }
                    if ($mform->elementExists('grade_item_aggregationcoef2')) {
                        $mform->removeElement('grade_item_aggregationcoef2');
                    }
                }
            }
        }
    }

    function validation($data, $files) {
        global $COURSE;
        $gradeitem = false;
        if ($data['id']) {
            $gradecategory = grade_category::fetch(array('id' => $data['id']));
            $gradeitem = $gradecategory->load_grade_item();
        }

        $errors = parent::validation($data, $files);

        if (array_key_exists('grade_item_gradetype', $data) and $data['grade_item_gradetype'] == GRADE_TYPE_SCALE) {
            if (empty($data['grade_item_scaleid'])) {
                $errors['grade_item_scaleid'] = get_string('missingscale', 'grades');
            }
        }
        if (array_key_exists('grade_item_grademin', $data) and array_key_exists('grade_item_grademax', $data)) {
            if (($data['grade_item_grademax'] != 0 OR $data['grade_item_grademin'] != 0) AND
                ($data['grade_item_grademax'] == $data['grade_item_grademin'] OR
                 $data['grade_item_grademax'] < $data['grade_item_grademin'])) {
                 $errors['grade_item_grademin'] = get_string('incorrectminmax', 'grades');
                 $errors['grade_item_grademax'] = get_string('incorrectminmax', 'grades');
             }
        }

        if ($data['id'] && $gradeitem->has_overridden_grades()) {
            if ($gradeitem->gradetype == GRADE_TYPE_VALUE) {
                if (grade_floats_different($data['grade_item_grademin'], $gradeitem->grademin) ||
                    grade_floats_different($data['grade_item_grademax'], $gradeitem->grademax)) {
                    if (empty($data['grade_item_rescalegrades'])) {
                        $errors['grade_item_rescalegrades'] = get_string('mustchooserescaleyesorno', 'grades');
                    }
                }
            }
        }
        return $errors;
    }
}


