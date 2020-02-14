<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php'); 

class workshop_edit_strategy_form extends moodleform {

    
    protected $strategy;

    
    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $this->workshop = $this->_customdata['workshop'];
        $this->strategy = $this->_customdata['strategy'];

        $mform->addElement('hidden', 'workshopid', $this->workshop->id);                $mform->setType('workshopid', PARAM_INT);

        $mform->addElement('hidden', 'strategy', $this->workshop->strategy);            $mform->setType('strategy', PARAM_PLUGIN);

        $this->definition_inner($mform);

                                        
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'saveandcontinue', get_string('saveandcontinue', 'workshop'));
        $buttonarray[] = $mform->createElement('submit', 'saveandpreview', get_string('saveandpreview', 'workshop'));
        $buttonarray[] = $mform->createElement('submit', 'saveandclose', get_string('saveandclose', 'workshop'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    
    final public function validation($data, $files) {
        return array_merge(
            parent::validation($data, $files),
            $this->validation_inner($data, $files)
        );
    }

    
    protected function definition_inner(&$mform) {
            }

    
    protected function validation_inner($data, $files) {
        return array();
    }
}
