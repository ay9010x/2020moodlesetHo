<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class book_chapter_edit_form extends moodleform {

    function definition() {
        global $CFG;

        $chapter = $this->_customdata['chapter'];
        $options = $this->_customdata['options'];

                $disabledmsg = null;
        if ($chapter->pagenum == 1) {
            $disabledmsg = get_string('subchapternotice', 'book');
        }

        $mform = $this->_form;

        if (!empty($chapter->id)) {
            $mform->addElement('header', 'general', get_string('editingchapter', 'mod_book'));
        } else {
            $mform->addElement('header', 'general', get_string('addafter', 'mod_book'));
        }

        $mform->addElement('text', 'title', get_string('chaptertitle', 'mod_book'), array('size'=>'30'));
        $mform->setType('title', PARAM_RAW);
        $mform->addRule('title', null, 'required', null, 'client');

        $mform->addElement('advcheckbox', 'subchapter', get_string('subchapter', 'mod_book'), $disabledmsg);

        $mform->addElement('editor', 'content_editor', get_string('content', 'mod_book'), null, $options);
        $mform->setType('content_editor', PARAM_RAW);
        $mform->addRule('content_editor', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'pagenum');
        $mform->setType('pagenum', PARAM_INT);

        $this->add_action_buttons(true);

                $this->set_data($chapter);
    }

    function definition_after_data(){
        $mform = $this->_form;
        $pagenum = $mform->getElement('pagenum');
        if ($pagenum->getValue() == 1) {
            $mform->hardFreeze('subchapter');
        }
    }
}
