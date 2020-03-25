<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once("$CFG->libdir/formslib.php");



class mod_forum_prune_form extends moodleform {

    
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('discussionname', 'forum'), array('size' => '60', 'maxlength' => '255'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->add_action_buttons(true, get_string('prune', 'forum'));

        $mform->addElement('hidden', 'prune');
        $mform->setType('prune', PARAM_INT);
        $mform->setConstant('prune', $this->_customdata['prune']);

        $mform->addElement('hidden', 'confirm');
        $mform->setType('confirm', PARAM_INT);
        $mform->setConstant('confirm', $this->_customdata['confirm']);
    }
}
