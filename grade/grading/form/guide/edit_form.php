<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once(dirname(__FILE__).'/guideeditor.php');
MoodleQuickForm::registerElementType('guideeditor', $CFG->dirroot.'/grade/grading/form/guide/guideeditor.php',
    'moodlequickform_guideeditor');


class gradingform_guide_editguide extends moodleform {

    
    public function definition() {
        $form = $this->_form;

        $form->addElement('hidden', 'areaid');
        $form->setType('areaid', PARAM_INT);

        $form->addElement('hidden', 'returnurl');
        $form->setType('returnurl', PARAM_LOCALURL);

                $form->addElement('text', 'name', get_string('name', 'gradingform_guide'),
            array('size' => 52, 'maxlength' => 255));
        $form->addRule('name', get_string('required'), 'required', null, 'client');
        $form->setType('name', PARAM_TEXT);
        $form->addRule('name', null, 'maxlength', 255, 'client');

                $options = gradingform_guide_controller::description_form_field_options($this->_customdata['context']);
        $form->addElement('editor', 'description_editor', get_string('description'), null, $options);
        $form->setType('description_editor', PARAM_RAW);

                $choices = array();
        $choices[gradingform_controller::DEFINITION_STATUS_DRAFT]    = html_writer::tag('span',
            get_string('statusdraft', 'core_grading'), array('class' => 'status draft'));
        $choices[gradingform_controller::DEFINITION_STATUS_READY]    = html_writer::tag('span',
            get_string('statusready', 'core_grading'), array('class' => 'status ready'));
        $form->addElement('select', 'status', get_string('guidestatus', 'gradingform_guide'), $choices)->freeze();

                $element = $form->addElement('guideeditor', 'guide', get_string('pluginname', 'gradingform_guide'));
        $form->setType('guide', PARAM_RAW);

        $buttonarray = array();
        $buttonarray[] = &$form->createElement('submit', 'saveguide', get_string('saveguide', 'gradingform_guide'));
        if ($this->_customdata['allowdraft']) {
            $buttonarray[] = &$form->createElement('submit', 'saveguidedraft', get_string('saveguidedraft', 'gradingform_guide'));
        }
        $editbutton = &$form->createElement('submit', 'editguide', ' ');
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
                $this->findbutton('saveguide')->setValue(get_string('save', 'gradingform_guide'));
            }
        }
    }

    
    public function validation($data, $files) {
        $err = parent::validation($data, $files);
        $err = array();
        $form = $this->_form;
        $guideel = $form->getElement('guide');
        if ($guideel->non_js_button_pressed($data['guide'])) {
                        $err['guidedummy'] = 1;
        } else if (isset($data['editguide'])) {
                        $err['guidedummy'] = 1;
        } else if ((isset($data['saveguide']) && $data['saveguide']) ||
                   (isset($data['saveguidedraft']) && $data['saveguidedraft'])) {
                        if ($guideel->validate($data['guide']) !== false) {
                $err['guidedummy'] = 1;
            }
        }
        return $err;
    }

    
    public function get_data() {
        $data = parent::get_data();
        if (!empty($data->saveguide)) {
            $data->status = gradingform_controller::DEFINITION_STATUS_READY;
        } else if (!empty($data->saveguidedraft)) {
            $data->status = gradingform_controller::DEFINITION_STATUS_DRAFT;
        }
        return $data;
    }

    
    public function need_confirm_regrading($controller) {
        $data = $this->get_data();
        if (isset($data->guide['regrade'])) {
                        return false;
        }
        if (!isset($data->saveguide) || !$data->saveguide) {
                        return false;
        }
        if (!$controller->has_active_instances()) {
                        return false;
        }
        $changelevel = $controller->update_or_check_guide($data);
        if ($changelevel == 0) {
                        return false;
        }

                        $form = $this->_form;
        foreach (array('guide', 'name') as $fieldname) {
            $el =& $form->getElement($fieldname);
            $el->freeze();
            $el->setPersistantFreeze(true);
            if ($fieldname == 'guide') {
                $el->add_regrade_confirmation($changelevel);
            }
        }

                $this->findbutton('saveguide')->setValue(get_string('continue'));
        $el =& $this->findbutton('editguide');
        $el->setValue(get_string('backtoediting', 'gradingform_guide'));
        $el->unfreeze();

        return true;
    }

    
    protected function &findbutton($elementname) {
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
