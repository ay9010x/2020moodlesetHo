<?php



defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

class admin_uploadpicture_form extends moodleform {
    function definition (){
        global $CFG, $USER;

        $mform =& $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('upload'));


        $options = array();
        $options['accepted_types'] = array('archive');
        $mform->addElement('filepicker', 'userpicturesfile', get_string('file'), 'size="40"', $options);
        $mform->addRule('userpicturesfile', null, 'required');

        $choices =& $this->_customdata;
        $mform->addElement('select', 'userfield', get_string('uploadpicture_userfield', 'tool_uploaduser'), $choices);
        $mform->setType('userfield', PARAM_INT);

        $choices = array( 0 => get_string('no'), 1 => get_string('yes') );
        $mform->addElement('select', 'overwritepicture', get_string('uploadpicture_overwrite', 'tool_uploaduser'), $choices);
        $mform->setType('overwritepicture', PARAM_INT);

        $this->add_action_buttons(false, get_string('uploadpictures', 'tool_uploaduser'));
    }
}

