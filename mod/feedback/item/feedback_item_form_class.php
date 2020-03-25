<?php

require_once($CFG->libdir.'/formslib.php');

define('FEEDBACK_ITEM_NAME_TEXTBOX_SIZE', 80);
define('FEEDBACK_ITEM_LABEL_TEXTBOX_SIZE', 20);
abstract class feedback_item_form extends moodleform {
    public function definition() {
        $item = $this->_customdata['item']; 
                                                        $common = $this->_customdata['common'];

                $positionlist = $this->_customdata['positionlist'];

                $position = $this->_customdata['position'];

        $mform =& $this->_form;

        if (array_filter(array_keys($common['items']))) {
            $mform->addElement('select',
                                'dependitem',
                                get_string('dependitem', 'feedback').'&nbsp;',
                                $common['items']
                                );
            $mform->addHelpButton('dependitem', 'depending', 'feedback');
            $mform->addElement('text',
                                'dependvalue',
                                get_string('dependvalue', 'feedback'),
                                array('size'=>FEEDBACK_ITEM_LABEL_TEXTBOX_SIZE, 'maxlength'=>255));
            $mform->disabledIf('dependvalue', 'dependitem', 'eq', '0');
        } else {
            $mform->addElement('hidden', 'dependitem', 0);
            $mform->addElement('hidden', 'dependvalue', '');
        }

        $mform->setType('dependitem', PARAM_INT);
        $mform->setType('dependvalue', PARAM_RAW);

        $position_select = $mform->addElement('select',
                                            'position',
                                            get_string('position', 'feedback').'&nbsp;',
                                            $positionlist);
        $position_select->setValue($position);

        $mform->addElement('hidden', 'cmid', $common['cmid']);
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'id', $common['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'feedback', $common['feedback']);
        $mform->setType('feedback', PARAM_INT);

        $mform->addElement('hidden', 'template', 0);
        $mform->setType('template', PARAM_INT);

        $mform->setType('name', PARAM_RAW);
        $mform->setType('label', PARAM_NOTAGS);

        $mform->addElement('hidden', 'typ', $this->type);
        $mform->setType('typ', PARAM_ALPHA);

        $mform->addElement('hidden', 'hasvalue', 0);
        $mform->setType('hasvalue', PARAM_INT);

        $mform->addElement('hidden', 'options', '');
        $mform->setType('options', PARAM_ALPHA);

        $buttonarray = array();
        if (!empty($item->id)) {
            $buttonarray[] = &$mform->createElement('submit',
                                                    'update_item',
                                                    get_string('update_item', 'feedback'));

            $buttonarray[] = &$mform->createElement('submit',
                                                    'clone_item',
                                                    get_string('save_as_new_item', 'feedback'));
        } else {
            $mform->addElement('hidden', 'clone_item', 0);
            $mform->setType('clone_item', PARAM_INT);
            $buttonarray[] = &$mform->createElement('submit',
                                                    'save_item',
                                                    get_string('save_item', 'feedback'));
        }
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(' '), false);

    }

    
    public function get_data() {
        if ($item = parent::get_data()) {
            if (!isset($item->dependvalue)) {
                $item->dependvalue = '';
            }
        }
        return $item;
    }
}

