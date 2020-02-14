<?php

require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_form_class.php');

class feedback_label_form extends feedback_item_form {
    protected $type = "label";
    private $area;

    public function definition() {
        global $CFG;

        $item = $this->_customdata['item'];
        $common = $this->_customdata['common'];
        $presentationoptions = $this->_customdata['presentationoptions'];
        $positionlist = $this->_customdata['positionlist'];
        $position = $this->_customdata['position'];

        $mform =& $this->_form;

        $mform->addElement('hidden', 'required', 0);
        $mform->setType('required', PARAM_INT);
        $mform->addElement('hidden', 'name', 'label');
        $mform->setType('template', PARAM_ALPHA);
        $mform->addElement('hidden', 'label', '');
        $mform->setType('label', PARAM_ALPHA);

        $mform->addElement('header', 'general', get_string($this->type, 'feedback'));
        $mform->addElement('editor', 'presentation_editor', get_string('labelcontents', 'feedback'), null, $presentationoptions);
        $mform->setType('presentation_editor', PARAM_RAW);

        parent::definition();
        $this->set_data($item);

    }

}

