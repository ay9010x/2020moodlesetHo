<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/filelib.php');


class mod_workshop_mod_form extends moodleform_mod {

    
    protected $course = null;

    
    public function __construct($current, $section, $cm, $course) {
        $this->course = $course;
        parent::__construct($current, $section, $cm, $course);
    }

    
    public function definition() {
        global $CFG;

        $workshopconfig = get_config('workshop');
        $mform = $this->_form;

                $mform->addElement('header', 'general', get_string('general', 'form'));

                $label = get_string('workshopname', 'workshop');
        $mform->addElement('text', 'name', $label, array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

                $this->standard_intro_elements(get_string('introduction', 'workshop'));

                $mform->addElement('header', 'gradingsettings', get_string('gradingsettings', 'workshop'));
        $mform->setExpanded('gradingsettings');

        $label = get_string('strategy', 'workshop');
        $mform->addElement('select', 'strategy', $label, workshop::available_strategies_list());
        $mform->setDefault('strategy', $workshopconfig->strategy);
        $mform->addHelpButton('strategy', 'strategy', 'workshop');

        $grades = workshop::available_maxgrades_list();
        $gradecategories = grade_get_categories_menu($this->course->id);

        $label = get_string('submissiongrade', 'workshop');
        $mform->addGroup(array(
            $mform->createElement('select', 'grade', '', $grades),
            $mform->createElement('select', 'gradecategory', '', $gradecategories),
            ), 'submissiongradegroup', $label, ' ', false);
        $mform->setDefault('grade', $workshopconfig->grade);
        $mform->addHelpButton('submissiongradegroup', 'submissiongrade', 'workshop');

        $mform->addElement('text', 'submissiongradepass', get_string('gradetopasssubmission', 'workshop'));
        $mform->addHelpButton('submissiongradepass', 'gradepass', 'grades');
        $mform->setDefault('submissiongradepass', '');
        $mform->setType('submissiongradepass', PARAM_RAW);

        $label = get_string('gradinggrade', 'workshop');
        $mform->addGroup(array(
            $mform->createElement('select', 'gradinggrade', '', $grades),
            $mform->createElement('select', 'gradinggradecategory', '', $gradecategories),
            ), 'gradinggradegroup', $label, ' ', false);
        $mform->setDefault('gradinggrade', $workshopconfig->gradinggrade);
        $mform->addHelpButton('gradinggradegroup', 'gradinggrade', 'workshop');

        $mform->addElement('text', 'gradinggradepass', get_string('gradetopassgrading', 'workshop'));
        $mform->addHelpButton('gradinggradepass', 'gradepass', 'grades');
        $mform->setDefault('gradinggradepass', '');
        $mform->setType('gradinggradepass', PARAM_RAW);

        $options = array();
        for ($i = 5; $i >= 0; $i--) {
            $options[$i] = $i;
        }
        $label = get_string('gradedecimals', 'workshop');
        $mform->addElement('select', 'gradedecimals', $label, $options);
        $mform->setDefault('gradedecimals', $workshopconfig->gradedecimals);

                $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'workshop'));

        $label = get_string('instructauthors', 'workshop');
        $mform->addElement('editor', 'instructauthorseditor', $label, null,
                            workshop::instruction_editors_options($this->context));

        $options = array();
        for ($i = 7; $i >= 0; $i--) {
            $options[$i] = $i;
        }
        $label = get_string('nattachments', 'workshop');
        $mform->addElement('select', 'nattachments', $label, $options);
        $mform->setDefault('nattachments', 1);

        $label = get_string('allowedfiletypesforsubmission', 'workshop');
        $mform->addElement('text', 'submissionfiletypes', $label, array('maxlength' => 255, 'size' => 64));
        $mform->addHelpButton('submissionfiletypes', 'allowedfiletypesforsubmission', 'workshop');
        $mform->setType('submissionfiletypes', PARAM_TEXT);
        $mform->addRule('submissionfiletypes', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->disabledIf('submissionfiletypes', 'nattachments', 'eq', 0);

        $options = get_max_upload_sizes($CFG->maxbytes, $this->course->maxbytes, 0, $workshopconfig->maxbytes);
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'workshop'), $options);
        $mform->setDefault('maxbytes', $workshopconfig->maxbytes);
        $mform->disabledIf('maxbytes', 'nattachments', 'eq', 0);

        $label = get_string('latesubmissions', 'workshop');
        $text = get_string('latesubmissions_desc', 'workshop');
        $mform->addElement('checkbox', 'latesubmissions', $label, $text);
        $mform->addHelpButton('latesubmissions', 'latesubmissions', 'workshop');

                $mform->addElement('header', 'assessmentsettings', get_string('assessmentsettings', 'workshop'));

        $label = get_string('instructreviewers', 'workshop');
        $mform->addElement('editor', 'instructreviewerseditor', $label, null,
                            workshop::instruction_editors_options($this->context));

        $label = get_string('useselfassessment', 'workshop');
        $text = get_string('useselfassessment_desc', 'workshop');
        $mform->addElement('checkbox', 'useselfassessment', $label, $text);
        $mform->addHelpButton('useselfassessment', 'useselfassessment', 'workshop');

                $mform->addElement('header', 'feedbacksettings', get_string('feedbacksettings', 'workshop'));

        $mform->addElement('select', 'overallfeedbackmode', get_string('overallfeedbackmode', 'mod_workshop'), array(
            0 => get_string('overallfeedbackmode_0', 'mod_workshop'),
            1 => get_string('overallfeedbackmode_1', 'mod_workshop'),
            2 => get_string('overallfeedbackmode_2', 'mod_workshop')));
        $mform->addHelpButton('overallfeedbackmode', 'overallfeedbackmode', 'mod_workshop');
        $mform->setDefault('overallfeedbackmode', 1);

        $options = array();
        for ($i = 7; $i >= 0; $i--) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'overallfeedbackfiles', get_string('overallfeedbackfiles', 'workshop'), $options);
        $mform->setDefault('overallfeedbackfiles', 0);
        $mform->disabledIf('overallfeedbackfiles', 'overallfeedbackmode', 'eq', 0);

        $label = get_string('allowedfiletypesforoverallfeedback', 'workshop');
        $mform->addElement('text', 'overallfeedbackfiletypes', $label, array('maxlength' => 255, 'size' => 64));
        $mform->addHelpButton('overallfeedbackfiletypes', 'allowedfiletypesforoverallfeedback', 'workshop');
        $mform->setType('overallfeedbackfiletypes', PARAM_TEXT);
        $mform->addRule('overallfeedbackfiletypes', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->disabledIf('overallfeedbackfiletypes', 'overallfeedbackfiles', 'eq', 0);

        $options = get_max_upload_sizes($CFG->maxbytes, $this->course->maxbytes);
        $mform->addElement('select', 'overallfeedbackmaxbytes', get_string('overallfeedbackmaxbytes', 'workshop'), $options);
        $mform->setDefault('overallfeedbackmaxbytes', $workshopconfig->maxbytes);
        $mform->disabledIf('overallfeedbackmaxbytes', 'overallfeedbackmode', 'eq', 0);
        $mform->disabledIf('overallfeedbackmaxbytes', 'overallfeedbackfiles', 'eq', 0);

        $label = get_string('conclusion', 'workshop');
        $mform->addElement('editor', 'conclusioneditor', $label, null,
                            workshop::instruction_editors_options($this->context));
        $mform->addHelpButton('conclusioneditor', 'conclusion', 'workshop');

                $mform->addElement('header', 'examplesubmissionssettings', get_string('examplesubmissions', 'workshop'));

        $label = get_string('useexamples', 'workshop');
        $text = get_string('useexamples_desc', 'workshop');
        $mform->addElement('checkbox', 'useexamples', $label, $text);
        $mform->addHelpButton('useexamples', 'useexamples', 'workshop');

        $label = get_string('examplesmode', 'workshop');
        $options = workshop::available_example_modes_list();
        $mform->addElement('select', 'examplesmode', $label, $options);
        $mform->setDefault('examplesmode', $workshopconfig->examplesmode);
        $mform->disabledIf('examplesmode', 'useexamples');

                $mform->addElement('header', 'accesscontrol', get_string('availability', 'core'));

        $label = get_string('submissionstart', 'workshop');
        $mform->addElement('date_time_selector', 'submissionstart', $label, array('optional' => true));

        $label = get_string('submissionend', 'workshop');
        $mform->addElement('date_time_selector', 'submissionend', $label, array('optional' => true));

        $label = get_string('submissionendswitch', 'mod_workshop');
        $mform->addElement('checkbox', 'phaseswitchassessment', $label);
        $mform->disabledIf('phaseswitchassessment', 'submissionend[enabled]');
        $mform->addHelpButton('phaseswitchassessment', 'submissionendswitch', 'mod_workshop');

        $label = get_string('assessmentstart', 'workshop');
        $mform->addElement('date_time_selector', 'assessmentstart', $label, array('optional' => true));

        $label = get_string('assessmentend', 'workshop');
        $mform->addElement('date_time_selector', 'assessmentend', $label, array('optional' => true));

        $coursecontext = context_course::instance($this->course->id);
        plagiarism_get_form_elements_module($mform, $coursecontext, 'mod_workshop');

                $features = array('groups' => true, 'groupings' => true,
                'outcomes' => true, 'gradecat' => false, 'idnumber' => false);

        $this->standard_coursemodule_elements();

                $this->add_action_buttons();
    }

    
    public function data_preprocessing(&$data) {
        if ($this->current->instance) {
                        $draftitemid = file_get_submitted_draft_itemid('instructauthors');
            $data['instructauthorseditor']['text'] = file_prepare_draft_area($draftitemid, $this->context->id,
                                'mod_workshop', 'instructauthors', 0,
                                workshop::instruction_editors_options($this->context),
                                $data['instructauthors']);
            $data['instructauthorseditor']['format'] = $data['instructauthorsformat'];
            $data['instructauthorseditor']['itemid'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('instructreviewers');
            $data['instructreviewerseditor']['text'] = file_prepare_draft_area($draftitemid, $this->context->id,
                                'mod_workshop', 'instructreviewers', 0,
                                workshop::instruction_editors_options($this->context),
                                $data['instructreviewers']);
            $data['instructreviewerseditor']['format'] = $data['instructreviewersformat'];
            $data['instructreviewerseditor']['itemid'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('conclusion');
            $data['conclusioneditor']['text'] = file_prepare_draft_area($draftitemid, $this->context->id,
                                'mod_workshop', 'conclusion', 0,
                                workshop::instruction_editors_options($this->context),
                                $data['conclusion']);
            $data['conclusioneditor']['format'] = $data['conclusionformat'];
            $data['conclusioneditor']['itemid'] = $draftitemid;
        } else {
                        $draftitemid = file_get_submitted_draft_itemid('instructauthors');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructauthors', 0);                $data['instructauthorseditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);

            $draftitemid = file_get_submitted_draft_itemid('instructreviewers');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructreviewers', 0);                $data['instructreviewerseditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);

            $draftitemid = file_get_submitted_draft_itemid('conclusion');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'conclusion', 0);                $data['conclusioneditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);
        }
    }

    
    public function definition_after_data() {

        $mform =& $this->_form;

        if ($id = $mform->getElementValue('update')) {
            $instance   = $mform->getElementValue('instance');

            $gradeitems = grade_item::fetch_all(array(
                'itemtype'      => 'mod',
                'itemmodule'    => 'workshop',
                'iteminstance'  => $instance,
                'courseid'      => $this->course->id));

            if (!empty($gradeitems)) {
                foreach ($gradeitems as $gradeitem) {
                                                            $decimalpoints = $gradeitem->get_decimals();
                    if ($gradeitem->itemnumber == 0) {
                        $submissiongradepass = $mform->getElement('submissiongradepass');
                        $submissiongradepass->setValue(format_float($gradeitem->gradepass, $decimalpoints));
                        $group = $mform->getElement('submissiongradegroup');
                        $elements = $group->getElements();
                        foreach ($elements as $element) {
                            if ($element->getName() == 'gradecategory') {
                                $element->setValue($gradeitem->categoryid);
                            }
                        }
                    } else if ($gradeitem->itemnumber == 1) {
                        $gradinggradepass = $mform->getElement('gradinggradepass');
                        $gradinggradepass->setValue(format_float($gradeitem->gradepass, $decimalpoints));
                        $group = $mform->getElement('gradinggradegroup');
                        $elements = $group->getElements();
                        foreach ($elements as $element) {
                            if ($element->getName() == 'gradinggradecategory') {
                                $element->setValue($gradeitem->categoryid);
                            }
                        }
                    }
                }
            }
        }

        parent::definition_after_data();
    }

    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

                foreach (array('submissionfiletypes', 'overallfeedbackfiletypes') as $fieldname) {
            if (isset($data[$fieldname])) {
                $invalidextensions = workshop::invalid_file_extensions($data[$fieldname], array_keys(core_filetypes::get_types()));
                if ($invalidextensions) {
                    $errors[$fieldname] = get_string('err_unknownfileextension', 'mod_workshop',
                        workshop::clean_file_extensions($invalidextensions));
                }
            }
        }

                if ($data['submissionstart'] > 0 and $data['submissionend'] > 0 and $data['submissionstart'] >= $data['submissionend']) {
            $errors['submissionend'] = get_string('submissionendbeforestart', 'mod_workshop');
        }
        if ($data['assessmentstart'] > 0 and $data['assessmentend'] > 0 and $data['assessmentstart'] >= $data['assessmentend']) {
            $errors['assessmentend'] = get_string('assessmentendbeforestart', 'mod_workshop');
        }

                if (max($data['submissionstart'], $data['submissionend']) > 0 and max($data['assessmentstart'], $data['assessmentend']) > 0) {
            $phasesubmissionend = max($data['submissionstart'], $data['submissionend']);
            $phaseassessmentstart = min($data['assessmentstart'], $data['assessmentend']);
            if ($phaseassessmentstart == 0) {
                $phaseassessmentstart = max($data['assessmentstart'], $data['assessmentend']);
            }
            if ($phasesubmissionend > 0 and $phaseassessmentstart > 0 and $phaseassessmentstart < $phasesubmissionend) {
                foreach (array('submissionend', 'submissionstart', 'assessmentstart', 'assessmentend') as $f) {
                    if ($data[$f] > 0) {
                        $errors[$f] = get_string('phasesoverlap', 'mod_workshop');
                        break;
                    }
                }
            }
        }

                if (!empty($data['submissiongradepass'])) {
            $submissiongradefloat = unformat_float($data['submissiongradepass'], true);
            if ($submissiongradefloat === false) {
                $errors['submissiongradepass'] = get_string('err_numeric', 'form');
            } else {
                if ($submissiongradefloat > $data['grade']) {
                    $errors['submissiongradepass'] = get_string('gradepassgreaterthangrade', 'grades', $data['grade']);
                }
            }
        }

                if (!empty($data['gradinggradepass'])) {
            $gradepassfloat = unformat_float($data['gradinggradepass'], true);
            if ($gradepassfloat === false) {
                $errors['gradinggradepass'] = get_string('err_numeric', 'form');
            } else {
                if ($gradepassfloat > $data['gradinggrade']) {
                    $errors['gradinggradepass'] = get_string('gradepassgreaterthangrade', 'grades', $data['gradinggrade']);
                }
            }
        }

        return $errors;
    }
}
