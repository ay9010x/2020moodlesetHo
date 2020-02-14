<?php




class block_glossary_random_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $DB;

                $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('title', 'block_glossary_random'));
        $mform->setDefault('config_title', get_string('pluginname','block_glossary_random'));
        $mform->setType('config_title', PARAM_TEXT);

                $glossaries = $DB->get_records_select_menu('glossary', 'course = ? OR globalglossary = ?', array($this->block->course->id, 1), 'name', 'id,name');
        foreach($glossaries as $key => $value) {
            $glossaries[$key] = strip_tags(format_string($value, true));
        }
        $mform->addElement('select', 'config_glossary', get_string('select_glossary', 'block_glossary_random'), $glossaries);

        $mform->addElement('text', 'config_refresh', get_string('refresh', 'block_glossary_random'), array('size' => 5));
        $mform->setDefault('config_refresh', 0);
        $mform->setType('config_refresh', PARAM_INT);

                $types = array(
            0 => get_string('random','block_glossary_random'),
            1 => get_string('lastmodified','block_glossary_random'),
            2 => get_string('nextone','block_glossary_random'),
            3 => get_string('nextalpha','block_glossary_random')
        );
        $mform->addElement('select', 'config_type', get_string('type', 'block_glossary_random'), $types);

        $mform->addElement('selectyesno', 'config_showconcept', get_string('showconcept', 'block_glossary_random'));
        $mform->setDefault('config_showconcept', 1);

        $mform->addElement('static', 'footerdescription', '', get_string('whichfooter', 'block_glossary_random'));

        $mform->addElement('text', 'config_addentry', get_string('askaddentry', 'block_glossary_random'));
        $mform->setDefault('config_addentry', get_string('addentry', 'block_glossary_random'));
        $mform->setType('config_addentry', PARAM_NOTAGS);

        $mform->addElement('text', 'config_viewglossary', get_string('askviewglossary', 'block_glossary_random'));
        $mform->setDefault('config_viewglossary', get_string('viewglossary', 'block_glossary_random'));
        $mform->setType('config_viewglossary', PARAM_NOTAGS);

        $mform->addElement('text', 'config_invisible', get_string('askinvisible', 'block_glossary_random'));
        $mform->setDefault('config_invisible', get_string('invisible', 'block_glossary_random'));
        $mform->setType('config_invisible', PARAM_NOTAGS);
    }
}
