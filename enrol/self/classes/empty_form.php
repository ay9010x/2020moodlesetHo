<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_self_empty_form extends moodleform {

    
    public function definition() {
        $this->_form->addElement('header', 'selfheader', $this->_customdata->header);
        $this->_form->addElement('static', 'info', '', $this->_customdata->info);
    }
}
