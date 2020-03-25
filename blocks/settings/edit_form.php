<?php




class block_settings_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $yesnooptions = array('yes'=>get_string('yes'), 'no'=>get_string('no'));

        $mform->addElement('select', 'config_enabledock', get_string('enabledock', $this->block->blockname), $yesnooptions);
        if (empty($this->block->config->enabledock) || $this->block->config->enabledock=='yes') {
            $mform->getElement('config_enabledock')->setSelected('yes');
        } else {
            $mform->getElement('config_enabledock')->setSelected('no');
        }
    }
}