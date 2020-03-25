<?php



namespace local_unittests\namespaced_form;

defined('MOODLE_INTERNAL') || die();


class exampleform extends \moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('text', 'title', 'title_value');
        $mform->setType('title', PARAM_TEXT);
    }
}
