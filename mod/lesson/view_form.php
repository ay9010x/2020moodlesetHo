<?php




defined('MOODLE_INTERNAL') || die();



require_once($CFG->libdir.'/formslib.php');


class lesson_page_without_answers extends moodleform {

    public function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        $title = $this->_customdata['title'];
        $contents = $this->_customdata['contents'];

        if (!empty($title)) {
            $mform->addElement('header', 'pageheader', $title);
        }

        if (!empty($contents)) {
            $mform->addElement('html', $OUTPUT->box($contents, 'contents'));
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'pageid');
        $mform->setType('pageid', PARAM_INT);

        $mform->addElement('hidden', 'newpageid');
        $mform->setType('newpageid', PARAM_INT);

        $this->add_action_buttons(null, get_string("continue", "lesson"));

    }

}
