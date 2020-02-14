<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();


class template extends persistent {

    protected static $persistentclass = 'core_competency\\template';

    
    public function definition() {
        $mform = $this->_form;

        $context = $this->_customdata['context'];

        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->setConstant('contextid', $context->id);

        $mform->addElement('header', 'generalhdr', get_string('general'));

                $mform->addElement('text', 'shortname', get_string('shortname', 'tool_lp'), 'maxlength="100"');
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', null, 'required', null, 'client');
        $mform->addRule('shortname', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
                $mform->addElement('editor', 'description',
                           get_string('description', 'tool_lp'), array('rows' => 4));
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('selectyesno', 'visible',
                           get_string('visible', 'tool_lp'));
        $mform->addElement('date_time_selector',
                           'duedate',
                           get_string('duedate', 'tool_lp'),
                           array('optional' => true));
        $mform->addHelpButton('duedate', 'duedate', 'tool_lp');

        $mform->setDefault('visible', true);
        $mform->addHelpButton('visible', 'visible', 'tool_lp');

        $mform->addElement('static', 'context', get_string('category', 'tool_lp'));
        $mform->setDefault('context', $context->get_context_name(false));
                $mform->setDisableShortforms();

        $this->add_action_buttons(true, get_string('savechanges', 'tool_lp'));

    }

}
