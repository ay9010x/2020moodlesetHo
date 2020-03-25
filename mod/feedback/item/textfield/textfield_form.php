<?php

require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_form_class.php');

class feedback_textfield_form extends feedback_item_form {
    protected $type = "textfield";

    public function definition() {
        $item = $this->_customdata['item'];
        $common = $this->_customdata['common'];
        $positionlist = $this->_customdata['positionlist'];
        $position = $this->_customdata['position'];

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string($this->type, 'feedback'));
        $mform->addElement('advcheckbox', 'required', get_string('required', 'feedback'), '' , null , array(0, 1));

        $mform->addElement('text',
                            'name',
                            get_string('item_name', 'feedback'),
                            array('size'=>FEEDBACK_ITEM_NAME_TEXTBOX_SIZE, 'maxlength'=>255));
        $mform->addElement('text',
                            'label',
                            get_string('item_label', 'feedback'),
                            array('size'=>FEEDBACK_ITEM_LABEL_TEXTBOX_SIZE, 'maxlength'=>255));

        $mform->addElement('select',
                            'itemsize',
                            get_string('textfield_size', 'feedback').'&nbsp;',
                            array_slice(range(0, 255), 5, 255, true));

        $mform->addElement('text',
                            'itemmaxlength',
                            get_string('textfield_maxlength', 'feedback'));
        $mform->setType('itemmaxlength', PARAM_INT);
        $mform->addRule('itemmaxlength', null, 'numeric', null, 'client');

        parent::definition();
        $this->set_data($item);

    }

    public function get_data() {
        if (!$item = parent::get_data()) {
            return false;
        }

        $item->presentation = $item->itemsize . '|'. $item->itemmaxlength;
        return $item;
    }
}

