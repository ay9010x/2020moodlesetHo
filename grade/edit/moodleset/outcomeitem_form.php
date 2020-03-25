<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';

class edit_outcomeitem_form extends moodleform {
    function definition() {
        global $COURSE, $CFG;

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string('gradeoutcomeitem', 'grades'));

        $mform->addElement('text', 'itemname', get_string('itemname', 'grades'));
        $mform->addRule('itemname', get_string('required'), 'required', null, 'client');
        $mform->setType('itemname', PARAM_TEXT);

        $mform->addElement('text', 'iteminfo', get_string('iteminfo', 'grades'));
        $mform->addHelpButton('iteminfo', 'iteminfo', 'grades');
        $mform->setType('iteminfo', PARAM_TEXT);

        $mform->addElement('text', 'idnumber', get_string('idnumbermod'));
        $mform->addHelpButton('idnumber', 'idnumbermod');
        $mform->setType('idnumber', PARAM_RAW);

                $options = array();
        if ($outcomes = grade_outcome::fetch_all_available($COURSE->id)) {
            foreach ($outcomes as $outcome) {
                $options[$outcome->id] = $outcome->get_name();
            }
        }
        $mform->addElement('selectwithlink', 'outcomeid', get_string('outcome', 'grades'), $options, null,
            array('link' => $CFG->wwwroot.'/grade/edit/outcome/course.php?id='.$COURSE->id, 'label' => get_string('outcomeassigntocourse', 'grades')));
        $mform->addHelpButton('outcomeid', 'outcome', 'grades');
        $mform->addRule('outcomeid', get_string('required'), 'required');

        $options = array(0=>get_string('none'));
        if ($coursemods = get_course_mods($COURSE->id)) {
            foreach ($coursemods as $coursemod) {
                if ($mod = get_coursemodule_from_id($coursemod->modname, $coursemod->id)) {
                    $options[$coursemod->id] = format_string($mod->name);
                }
            }
        }
        $mform->addElement('select', 'cmid', get_string('linkedactivity', 'grades'), $options);
        $mform->addHelpButton('cmid', 'linkedactivity', 'grades');
        $mform->setDefault('cmid', 0);

                        $mform->addElement('checkbox', 'hidden', get_string('hidden', 'grades'));
        $mform->addHelpButton('hidden', 'hidden', 'grades');
        $mform->addElement('date_time_selector', 'hiddenuntil', get_string('hiddenuntil', 'grades'), array('optional'=>true));
        $mform->disabledIf('hidden', 'hiddenuntil[off]', 'notchecked');

                $mform->addElement('advcheckbox', 'locked', get_string('locked', 'grades'));
        $mform->addHelpButton('locked', 'locked', 'grades');
        $mform->addElement('date_time_selector', 'locktime', get_string('locktime', 'grades'), array('optional'=>true));

        $mform->addElement('header', 'headerparent', get_string('parentcategory', 'grades'));

        $mform->addElement('advcheckbox', 'weightoverride', get_string('adjustedweight', 'grades'));
        $mform->addHelpButton('weightoverride', 'weightoverride', 'grades');

        $mform->addElement('text', 'aggregationcoef2', get_string('weight', 'grades'));
        $mform->addHelpButton('aggregationcoef2', 'weight', 'grades');
        $mform->setType('aggregationcoef2', PARAM_RAW);
        $mform->disabledIf('aggregationcoef2', 'weightoverride');

        $options = array();
        $default = '';
        $coefstring = '';
        $categories = grade_category::fetch_all(array('courseid'=>$COURSE->id));
        foreach ($categories as $cat) {
            $cat->apply_forced_settings();
            $options[$cat->id] = $cat->get_name();
            if ($cat->is_course_category()) {
                $default = $cat->id;
            }
            if ($cat->is_aggregationcoef_used()) {
                if ($cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
                    $coefstring = ($coefstring=='' or $coefstring=='aggregationcoefweight') ? 'aggregationcoefweight' : 'aggregationcoef';

                } else if ($cat->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
                    $coefstring = ($coefstring=='' or $coefstring=='aggregationcoefextrasum') ? 'aggregationcoefextrasum' : 'aggregationcoef';

                } else if ($cat->aggregation == GRADE_AGGREGATE_EXTRACREDIT_MEAN) {
                    $coefstring = ($coefstring=='' or $coefstring=='aggregationcoefextraweight') ? 'aggregationcoefextraweight' : 'aggregationcoef';

                } else if ($cat->aggregation == GRADE_AGGREGATE_SUM) {
                    $coefstring = ($coefstring=='' or $coefstring=='aggregationcoefextrasum') ? 'aggregationcoefextrasum' : 'aggregationcoef';

                } else {
                    $coefstring = 'aggregationcoef';
                }
            } else {
                $mform->disabledIf('aggregationcoef', 'parentcategory', 'eq', $cat->id);
            }
        }

        if (count($categories) > 1) {
            $mform->addElement('select', 'parentcategory', get_string('gradecategory', 'grades'), $options);
            $mform->disabledIf('parentcategory', 'cmid', 'noteq', 0);
        }

        if ($coefstring !== '') {
            if ($coefstring == 'aggregationcoefextrasum' || $coefstring == 'aggregationcoefextraweightsum') {
                                $coefstring = 'aggregationcoefextrasum';
                $mform->addElement('checkbox', 'aggregationcoef', get_string($coefstring, 'grades'));
            } else {
                $mform->addElement('text', 'aggregationcoef', get_string($coefstring, 'grades'));
            }
            $mform->addHelpButton('aggregationcoef', $coefstring, 'grades');
        }

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $gpr = $this->_customdata['gpr'];
        $gpr->add_mform_elements($mform);

        if (isset($CFG->grade_item_advanced)) {
            $advanced = explode(',', $CFG->grade_item_advanced);
            foreach ($advanced as $el) {
                if ($mform->elementExists($el)) {
                    $mform->setAdvanced($el);
                }
            }
        }
                $this->add_action_buttons();
    }


    function definition_after_data() {
        global $CFG, $COURSE;

        $mform =& $this->_form;

        if ($id = $mform->getElementValue('id')) {
            $grade_item = grade_item::fetch(array('id'=>$id));

                        if ($grade_item->is_course_item()) {
                if ($mform->elementExists('parentcategory')) {
                    $mform->removeElement('parentcategory');
                }
                if ($mform->elementExists('aggregationcoef')) {
                    $mform->removeElement('aggregationcoef');
                }

            } else {
                                if ($mform->elementExists('parentcategory')) {
                    $mform->hardFreeze('parentcategory');
                }

                if ($grade_item->is_category_item()) {
                    $category = $grade_item->get_item_category();
                    $parent_category = $category->get_parent_category();
                } else {
                    $parent_category = $grade_item->get_parent_category();
                }

                $parent_category->apply_forced_settings();

                if (!$parent_category->is_aggregationcoef_used() || !$parent_category->aggregateoutcomes) {
                    if ($mform->elementExists('aggregationcoef')) {
                        $mform->removeElement('aggregationcoef');
                    }
                } else {
                                        $agg_el =& $mform->getElement('aggregationcoef');
                    $aggcoef = '';
                    if ($parent_category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN) {
                        $aggcoef = 'aggregationcoefweight';

                    } else if ($parent_category->aggregation == GRADE_AGGREGATE_WEIGHTED_MEAN2) {
                        $aggcoef = 'aggregationcoefextrasum';

                    } else if ($parent_category->aggregation == GRADE_AGGREGATE_EXTRACREDIT_MEAN) {
                        $aggcoef = 'aggregationcoefextraweight';

                    } else if ($parent_category->aggregation == GRADE_AGGREGATE_SUM) {
                        $aggcoef = 'aggregationcoefextrasum';
                    }

                    if ($aggcoef !== '') {
                        $agg_el->setLabel(get_string($aggcoef, 'grades'));
                        $mform->addHelpButton('aggregationcoef', $aggcoef, 'grades');
                    }
                }

                                                if ($parent_category->aggregation != GRADE_AGGREGATE_SUM ||
                        !$parent_category->aggregateoutcomes) {
                    if ($mform->elementExists('weightoverride')) {
                        $mform->removeElement('weightoverride');
                    }
                    if ($mform->elementExists('aggregationcoef2')) {
                        $mform->removeElement('aggregationcoef2');
                    }
                }
            }

        }

                if (!$mform->elementExists('aggregationcoef') and !$mform->elementExists('parentcategory')) {
            $mform->removeElement('headerparent');
        }
    }


    function validation($data, $files) {
        global $COURSE;

        $errors = parent::validation($data, $files);

        if (array_key_exists('idnumber', $data)) {
            if ($data['id']) {
                $grade_item = new grade_item(array('id'=>$data['id'], 'courseid'=>$data['courseid']));
            } else {
                $grade_item = null;
            }
            if (!grade_verify_idnumber($data['idnumber'], $COURSE->id, $grade_item, null)) {
                $errors['idnumber'] = get_string('idnumbertaken');
            }
        }

        return $errors;
    }

}

