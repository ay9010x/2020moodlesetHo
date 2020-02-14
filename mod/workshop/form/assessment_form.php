<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php'); 

class workshop_assessment_form extends moodleform {

    
    public function definition() {
        global $CFG;

        $mform          = $this->_form;
        $this->mode     = $this->_customdata['mode'];               $this->strategy = $this->_customdata['strategy'];           $this->workshop = $this->_customdata['workshop'];           $this->options  = $this->_customdata['options'];    
                $mform->setDisableShortforms();

                $this->definition_inner($mform);

                $mform->addElement('hidden', 'strategy', $this->workshop->strategy);
        $mform->setType('strategy', PARAM_PLUGIN);

        if ($this->workshop->overallfeedbackmode and $this->is_editable()) {
            $mform->addElement('header', 'overallfeedbacksection', get_string('overallfeedback', 'mod_workshop'));
            $mform->addElement('editor', 'feedbackauthor_editor', get_string('feedbackauthor', 'mod_workshop'), null,
                $this->workshop->overall_feedback_content_options());
            if ($this->workshop->overallfeedbackmode == 2) {
                $mform->addRule('feedbackauthor_editor', null, 'required', null, 'client');
            }
            if ($this->workshop->overallfeedbackfiles) {
                $mform->addElement('filemanager', 'feedbackauthorattachment_filemanager',
                    get_string('feedbackauthorattachment', 'mod_workshop'), null,
                    $this->workshop->overall_feedback_attachment_options());
            }
        }

        if (!empty($this->options['editableweight']) and $this->is_editable()) {
            $mform->addElement('header', 'assessmentsettings', get_string('assessmentweight', 'workshop'));
            $mform->addElement('select', 'weight',
                    get_string('assessmentweight', 'workshop'), workshop::available_assessment_weights_list());
            $mform->setDefault('weight', 1);
        }

        $buttonarray = array();
        if ($this->mode == 'preview') {
            $buttonarray[] = $mform->createElement('cancel', 'backtoeditform', get_string('backtoeditform', 'workshop'));
        }
        if ($this->mode == 'assessment') {
            if (!empty($this->options['pending'])) {
                $buttonarray[] = $mform->createElement('submit', 'saveandshownext', get_string('saveandshownext', 'workshop'));
            }
            $buttonarray[] = $mform->createElement('submit', 'saveandclose', get_string('saveandclose', 'workshop'));
            $buttonarray[] = $mform->createElement('submit', 'saveandcontinue', get_string('saveandcontinue', 'workshop'));
            $buttonarray[] = $mform->createElement('cancel');
        }
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    
    protected function definition_inner(&$mform) {
            }

    
    public function is_editable() {
        return !$this->_form->isFrozen();
    }

    
    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        if (isset($data['feedbackauthorattachment_filemanager']) and isset($this->workshop->overallfeedbackfiletypes)) {
            $whitelist = workshop::normalize_file_extensions($this->workshop->overallfeedbackfiletypes);
            if ($whitelist) {
                $draftfiles = file_get_drafarea_files($data['feedbackauthorattachment_filemanager']);
                if ($draftfiles) {
                    $wrongfiles = array();
                    foreach ($draftfiles->list as $file) {
                        if (!workshop::is_allowed_file_type($file->filename, $whitelist)) {
                            $wrongfiles[] = $file->filename;
                        }
                    }
                    if ($wrongfiles) {
                        $a = array(
                            'whitelist' => workshop::clean_file_extensions($whitelist),
                            'wrongfiles' => implode(', ', $wrongfiles),
                        );
                        $errors['feedbackauthorattachment_filemanager'] = get_string('err_wrongfileextension', 'mod_workshop', $a);
                    }
                }
            }
        }

        return $errors;
    }
}
