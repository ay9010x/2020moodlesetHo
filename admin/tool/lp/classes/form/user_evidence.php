<?php



namespace tool_lp\form;
defined('MOODLE_INTERNAL') || die();


class user_evidence extends persistent {

    protected static $persistentclass = 'core_competency\\user_evidence';

    protected static $foreignfields = array('files');

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setConstant('userid', $this->_customdata['userid']);

        $mform->addElement('header', 'generalhdr', get_string('general'));

                $mform->addElement('text', 'name', get_string('userevidencename', 'tool_lp'), 'maxlength="100"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
                $mform->addElement('editor', 'description', get_string('userevidencedescription', 'tool_lp'), array('rows' => 10));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('url', 'url', get_string('userevidenceurl', 'tool_lp'), array(), array('usefilepicker' => false));
        $mform->setType('url', PARAM_RAW_TRIMMED);              $mform->addHelpButton('url', 'userevidenceurl', 'tool_lp');

        $mform->addElement('filemanager', 'files', get_string('userevidencefiles', 'tool_lp'), array(),
            $this->_customdata['fileareaoptions']);
                $mform->setDisableShortforms();

        $this->add_action_buttons();
    }

}
