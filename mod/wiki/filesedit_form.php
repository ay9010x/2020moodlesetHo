<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class mod_wiki_filesedit_form extends moodleform {
    protected function definition() {
        $mform = $this->_form;

        $data    = $this->_customdata['data'];
        $options = $this->_customdata['options'];
        $mform->addElement('header', 'general', get_string('editfiles', 'wiki'));
        $mform->addElement('filemanager', 'files_filemanager', get_string('files'), null, $options);

        $mform->addElement('hidden', 'returnurl', $data->returnurl);
        $mform->setType('returnurl', PARAM_URL);

        $mform->addElement('hidden', 'subwiki', $data->subwikiid);
        $mform->setType('subwiki', PARAM_INT);

        $this->add_action_buttons(true, get_string('savechanges'));

        $this->set_data($data);
    }
}
