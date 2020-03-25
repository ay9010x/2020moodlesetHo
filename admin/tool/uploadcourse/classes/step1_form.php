<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


class tool_uploadcourse_step1_form extends tool_uploadcourse_base_form {

    
    public function definition () {
        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));

        $mform->addElement('filepicker', 'coursefile', get_string('coursefile', 'tool_uploadcourse'));
        $mform->addRule('coursefile', null, 'required');
        $mform->addHelpButton('coursefile', 'coursefile', 'tool_uploadcourse');

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'tool_uploadcourse'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }
        $mform->addHelpButton('delimiter_name', 'csvdelimiter', 'tool_uploadcourse');

        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'tool_uploadcourse'), $choices);
        $mform->setDefault('encoding', 'UTF-8');
        $mform->addHelpButton('encoding', 'encoding', 'tool_uploadcourse');

        $choices = array('10' => 10, '20' => 20, '100' => 100, '1000' => 1000, '100000' => 100000);
        $mform->addElement('select', 'previewrows', get_string('rowpreviewnum', 'tool_uploadcourse'), $choices);
        $mform->setType('previewrows', PARAM_INT);
        $mform->addHelpButton('previewrows', 'rowpreviewnum', 'tool_uploadcourse');

        $this->add_import_options();

        $mform->addElement('hidden', 'showpreview', 1);
        $mform->setType('showpreview', PARAM_INT);

        $this->add_action_buttons(false, get_string('preview', 'tool_uploadcourse'));
    }
}
