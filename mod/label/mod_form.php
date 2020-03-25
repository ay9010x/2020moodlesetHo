<?php




defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_label_mod_form extends moodleform_mod {

    function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));
        $this->standard_intro_elements(get_string('labeltext', 'label'));

        $this->standard_coursemodule_elements();

        $this->add_action_buttons(true, false, null);

    }

}
