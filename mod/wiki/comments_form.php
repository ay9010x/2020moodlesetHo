<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->dirroot . '/lib/formslib.php');

class mod_wiki_comments_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $current = $this->_customdata['current'];
        $commentoptions = $this->_customdata['commentoptions'];

                $mform->addElement('editor', 'entrycomment_editor', get_string('comment', 'glossary'), null, $commentoptions);
        $mform->addRule('entrycomment_editor', get_string('required'), 'required', null, 'client');
        $mform->setType('entrycomment_editor', PARAM_RAW); 
                $mform->addElement('hidden', 'id', '');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action', '');
        $mform->setType('action', PARAM_ALPHAEXT);

                        $this->add_action_buttons(false);

                $this->set_data($current);
    }

    public function edit_definition($current, $commentoptions) {
        $this->set_data($current);
        $this->set_data($commentoptions);
    }
}

