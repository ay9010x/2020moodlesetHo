<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir.'/formslib.php');

class feedback_import_form extends moodleform {
    public function definition() {
        global $CFG;
        $mform =& $this->_form;

        $strdeleteolditmes = get_string('delete_old_items', 'feedback').
                             ' ('.get_string('oldvalueswillbedeleted', 'feedback').')';

        $strnodeleteolditmes = get_string('append_new_items', 'feedback').
                               ' ('.get_string('oldvaluespreserved', 'feedback').')';

        $mform->addElement('radio', 'deleteolditems', '', $strdeleteolditmes, true);
        $mform->addElement('radio', 'deleteolditems', '', $strnodeleteolditmes);

                $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('filepicker',
                           'choosefile',
                           get_string('file'),
                           null,
                           array('maxbytes' => $CFG->maxbytes, 'filetypes' => '*'));

                $this->add_action_buttons(true, get_string('yes'));

    }
}
