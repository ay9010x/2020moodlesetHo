<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once(dirname(__FILE__).'/rubriceditor.php');
MoodleQuickForm::registerElementType('rubriceditor', $CFG->dirroot.'/grade/grading/form/rubric/rubriceditor.php', 'MoodleQuickForm_rubriceditor');


class gradingform_rubric_editrubric extends moodleform {

    
    public function definition() {
        $form = $this->_form;

        $form->addElement('hidden', 'areaid');
        $form->setType('areaid', PARAM_INT);

        $form->addElement('hidden', 'returnurl');
        $form->setType('returnurl', PARAM_LOCALURL);

                $form->addElement('text', 'name', get_string('name', 'gradingform_rubric'), array('size' => 52, 'aria-required' => 'true'));
        $form->addRule('name', get_string('required'), 'required', null, 'client');
        $form->setType('name', PARAM_TEXT);

                $options = gradingform_rubric_controller::description_form_field_options($this->_customdata['context']);
        $form->addElement('editor', 'description_editor', get_string('description', 'gradingform_rubric'), null, $options);
        $form->setType('description_editor', PARAM_RAW);

                $choices = array();
        $choices[gradingform_controller::DEFINITION_STATUS_DRAFT]    = html_writer::tag('span', get_string('statusdraft', 'core_grading'), array('class' => 'status draft'));
        $choices[gradingform_controller::DEFINITION_STATUS_READY]    = html_writer::tag('span', get_string('statusready', 'core_grading'), array('class' => 'status ready'));
        $form->addElement('select', 'status', get_string('rubricstatus', 'gradingform_rubric'), $choices)->freeze();

                $element = $form->addElement('rubriceditor', 'rubric', get_string('rubric', 'gradingform_rubric'));
        $form->setType('rubric', PARAM_RAW);

        $buttonarray = array();
        $buttonarray[] = &$form->createElement('submit', 'saverubric', get_string('saverubric', 'gradingform_rubric'));
        if ($this->_customdata['allowdraft']) {
            $buttonarray[] = &$form->createElement('submit', 'saverubricdraft', get_string('saverubricdraft', 'gradingform_rubric'));
        }
        $editbutton = &$form->createElement('submit', 'editrubric', ' ');
        $editbutton->freeze();
        $buttonarray[] = &$editbutton;
        $buttonarray[] = &$form->createElement('cancel');
        $form->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $form->closeHeaderBefore('buttonar');
    }

    
    public function definition_after_data() {
        $form = $this->_form;
        $el = $form->getElement('status');
        if (!$el->getValue()) {
            $form->removeElement('status');
        } else {
            $vals = array_values($el->getValue());
            if ($vals[0] == gradingform_controller::DEFINITION_STATUS_READY) {
                $this->findButton('saverubric')->setValue(get_string('save', 'gradingform_rubric'));
            }
        }
    }

    
    public function validation($data, $files) {
        $err = parent::validation($data, $files);
        $err = array();
        $form = $this->_form;
        $rubricel = $form->getElement('rubric');
        if ($rubricel->non_js_button_pressed($data['rubric'])) {
                        $err['rubricdummy'] = 1;
        } else if (isset($data['editrubric'])) {
                        $err['rubricdummy'] = 1;
        } else if (isset($data['saverubric']) && $data['saverubric']) {
                        if ($rubricel->validate($data['rubric']) !== false) {
                $err['rubricdummy'] = 1;
            }
        }
        return $err;
    }

    
    public function get_data() {
        $data = parent::get_data();
        if (!empty($data->saverubric)) {
            $data->status = gradingform_controller::DEFINITION_STATUS_READY;
        } else if (!empty($data->saverubricdraft)) {
            $data->status = gradingform_controller::DEFINITION_STATUS_DRAFT;
        }
        return $data;
    }

    
    public function need_confirm_regrading($controller) {
        $data = $this->get_data();
        if (isset($data->rubric['regrade'])) {
                        return false;
        }
        if (!isset($data->saverubric) || !$data->saverubric) {
                        return false;
        }
        if (!$controller->has_active_instances()) {
                        return false;
        }
        $changelevel = $controller->update_or_check_rubric($data);
        if ($changelevel == 0) {
                        return false;
        }

                        $form = $this->_form;
        foreach (array('rubric', 'name') as $fieldname) {
            $el =& $form->getElement($fieldname);
            $el->freeze();
            $el->setPersistantFreeze(true);
            if ($fieldname == 'rubric') {
                $el->add_regrade_confirmation($changelevel);
            }
        }

                $this->findButton('saverubric')->setValue(get_string('continue'));
        $el =& $this->findButton('editrubric');
        $el->setValue(get_string('backtoediting', 'gradingform_rubric'));
        $el->unfreeze();

        return true;
    }

    
    protected function &findButton($elementname) {
        $form = $this->_form;
        $buttonar =& $form->getElement('buttonar');
        $elements =& $buttonar->getElements();
        foreach ($elements as $el) {
            if ($el->getName() == $elementname) {
                return $el;
            }
        }
        return null;
    }
}
