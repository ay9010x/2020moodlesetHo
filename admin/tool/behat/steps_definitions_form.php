<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


class steps_definitions_form extends moodleform {

    
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'filters', get_string('stepsdefinitionsfilters', 'tool_behat'));

        $types = array(
            '' => get_string('allavailablesteps', 'tool_behat'),
            'given' => get_string('giveninfo', 'tool_behat'),
            'when' => get_string('wheninfo', 'tool_behat'),
            'then' => get_string('theninfo', 'tool_behat')
        );
        $mform->addElement('select', 'type', get_string('stepsdefinitionstype', 'tool_behat'), $types);

        $mform->addElement(
            'select',
            'component',
            get_string('stepsdefinitionscomponent', 'tool_behat'),
            $this->_customdata['components']
        );

        $mform->addElement('text', 'filter', get_string('stepsdefinitionscontains', 'tool_behat'));
        $mform->setType('filter', PARAM_NOTAGS);

        $mform->addElement('submit', 'submit', get_string('viewsteps', 'tool_behat'));
    }
}
