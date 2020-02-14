<?php




defined('MOODLE_INTERNAL') || die();


class lesson_import_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        $mform->addElement('select', 'format', get_string('fileformat', 'lesson'), $this->_customdata['formats']);
        $mform->setDefault('format', 'gift');
        $mform->setType('format', 'text');
        $mform->addRule('format', null, 'required');

                $mform->addElement('filepicker', 'questionfile', get_string('upload'));
        $mform->addRule('questionfile', null, 'required', null, 'client');

        $this->add_action_buttons(null, get_string("import"));
    }

    
    protected function validate_uploaded_file($data, $errors) {
        global $CFG;

        if (empty($data['questionfile'])) {
            $errors['questionfile'] = get_string('required');
            return $errors;
        }

        $files = $this->get_draft_files('questionfile');
        if (count($files) < 1) {
            $errors['questionfile'] = get_string('required');
            return $errors;
        }

        $formatfile = $CFG->dirroot.'/question/format/'.$data['format'].'/format.php';
        if (!is_readable($formatfile)) {
            throw new moodle_exception('formatnotfound', 'lesson', '', $data['format']);
        }

        require_once($formatfile);

        $classname = 'qformat_' . $data['format'];
        $qformat = new $classname();

        return $errors;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $errors = $this->validate_uploaded_file($data, $errors);
        return $errors;
    }
}