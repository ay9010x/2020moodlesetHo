<?php




defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');   require_once(dirname(dirname(__FILE__)).'/edit_form.php');    

class workshop_edit_comments_strategy_form extends workshop_edit_strategy_form {

    
    protected function definition_inner(&$mform) {

        $norepeats          = $this->_customdata['norepeats'];                  $descriptionopts    = $this->_customdata['descriptionopts'];            $current            = $this->_customdata['current'];            
        $mform->addElement('hidden', 'norepeats', $norepeats);
        $mform->setType('norepeats', PARAM_INT);
                $mform->setConstants(array('norepeats' => $norepeats));

        for ($i = 0; $i < $norepeats; $i++) {
            $mform->addElement('header', 'dimension'.$i, get_string('dimensionnumber', 'workshopform_comments', $i+1));
            $mform->addElement('hidden', 'dimensionid__idx_'.$i);
            $mform->setType('dimensionid__idx_'.$i, PARAM_INT);
            $mform->addElement('editor', 'description__idx_'.$i.'_editor',
                                get_string('dimensiondescription', 'workshopform_comments'), '', $descriptionopts);
        }

        $mform->registerNoSubmitButton('noadddims');
        $mform->addElement('submit', 'noadddims', get_string('addmoredimensions', 'workshopform_comments',
                workshop_comments_strategy::ADDDIMS));
        $mform->closeHeaderBefore('noadddims');
        $this->set_data($current);
    }
}
