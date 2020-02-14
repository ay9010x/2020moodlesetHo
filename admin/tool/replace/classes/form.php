<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


class tool_replace_form extends moodleform {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'searchhdr', get_string('pluginname', 'tool_replace'));
        $mform->setExpanded('searchhdr', true);

        $mform->addElement('text', 'search', get_string('searchwholedb', 'tool_replace'), 'size="50"');
        $mform->setType('search', PARAM_RAW);
        $mform->addElement('static', 'searchst', '', get_string('searchwholedbhelp', 'tool_replace'));
        $mform->addRule('search', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'replace', get_string('replacewith', 'tool_replace'), 'size="50"', PARAM_RAW);
        $mform->addElement('static', 'replacest', '', get_string('replacewithhelp', 'tool_replace'));
        $mform->setType('replace', PARAM_RAW);
        $mform->addElement('checkbox', 'shorten', get_string('shortenoversized', 'tool_replace'));
        $mform->addRule('replace', get_string('required'), 'required', null, 'client');

        $mform->addElement('header', 'confirmhdr', get_string('confirm'));
        $mform->setExpanded('confirmhdr', true);
        $mform->addElement('checkbox', 'sure', get_string('disclaimer', 'tool_replace'));
        $mform->addRule('sure', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('doit', 'tool_replace'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['shorten']) and core_text::strlen($data['search']) < core_text::strlen($data['replace'])) {
            $errors['shorten'] = get_string('required');
        }

        return $errors;
    }
}
