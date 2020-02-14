<?php




class block_html_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG;

                $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_html'));
        $mform->setType('config_title', PARAM_TEXT);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->block->context);
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_html'), null, $editoroptions);
        $mform->addRule('config_text', null, 'required', null, 'client');
        $mform->setType('config_text', PARAM_RAW); 
        if (!empty($CFG->block_html_allowcssclasses)) {
            $mform->addElement('text', 'config_classes', get_string('configclasses', 'block_html'));
            $mform->setType('config_classes', PARAM_TEXT);
            $mform->addHelpButton('config_classes', 'configclasses', 'block_html');
        }
    }

    function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->text;
            $draftid_editor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_html', 'content', 0, array('subdirs'=>true), $currenttext);
            $defaults->config_text['itemid'] = $draftid_editor;
            $defaults->config_text['format'] = $this->block->config->format;
        } else {
            $text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
                        $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
                        unset($this->block->config->title);
        }

                        unset($this->block->config->text);
        parent::set_data($defaults);
                if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        $this->block->config->text = $text;
        if (isset($title)) {
                        $this->block->config->title = $title;
        }
    }
}
