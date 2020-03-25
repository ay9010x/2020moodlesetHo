<?php




class block_tag_youtube_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_tag_youtube'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('text', 'config_numberofvideos', get_string('numberofvideos', 'block_tag_youtube'), array('size' => 5));
        $mform->setType('config_numberofvideos', PARAM_INT);

        $categorychoices = $this->block->get_categories();
        $mform->addElement('select', 'config_category', get_string('category', 'block_tag_youtube'), $categorychoices);
        $mform->setDefault('config_category', 0);

        $mform->addElement('text', 'config_playlist', get_string('includeonlyvideosfromplaylist', 'block_tag_youtube'));
        $mform->setType('config_playlist', PARAM_ALPHANUM);
    }
}
