<?php




class block_navigation_edit_form extends block_edit_form {
    
    protected function specific_definition($mform) {
        global $CFG;
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mods = array('enabledock'=>'yes', 'linkcategories'=>'no');
        $yesnooptions = array('yes'=>get_string('yes'), 'no'=>get_string('no'));
        foreach ($mods as $modname=>$default) {
            $mform->addElement('select', 'config_'.$modname, get_string($modname.'desc', $this->block->blockname), $yesnooptions);
            $mform->setDefault('config_'.$modname, $default);
        }

        $options = array(
            block_navigation::TRIM_RIGHT => get_string('trimmoderight', $this->block->blockname),
            block_navigation::TRIM_LEFT => get_string('trimmodeleft', $this->block->blockname),
            block_navigation::TRIM_CENTER => get_string('trimmodecenter', $this->block->blockname)
        );
        $mform->addElement('select', 'config_trimmode', get_string('trimmode', $this->block->blockname), $options);
        $mform->setType('config_trimmode', PARAM_INT);

        $mform->addElement('text', 'config_trimlength', get_string('trimlength', $this->block->blockname));
        $mform->setDefault('config_trimlength', 50);
        $mform->setType('config_trimlength', PARAM_INT);

        $options = array(
            0 => get_string('everything', $this->block->blockname),
            global_navigation::TYPE_COURSE => get_string('courses', $this->block->blockname),
            global_navigation::TYPE_SECTION => get_string('coursestructures', $this->block->blockname),
            global_navigation::TYPE_ACTIVITY => get_string('courseactivities', $this->block->blockname)
        );
        $mform->addElement('select', 'config_expansionlimit', get_string('expansionlimit', $this->block->blockname), $options);
        $mform->setType('config_expansionlimit', PARAM_INT);

    }
}