<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');


class assignfeedback_file_upload_zip_form extends moodleform {
    
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;
        $params = $this->_customdata;

        $mform->addElement('header', 'uploadzip', get_string('uploadzip', 'assignfeedback_file'));

        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$COURSE->maxbytes,
                                'accepted_types'=>'zip',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'feedbackzip', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('feedbackzip', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('feedbackzip', 'feedbackzip', 'assignfeedback_file');

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'uploadzip');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'file');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $this->add_action_buttons(true, get_string('importfeedbackfiles', 'assignfeedback_file'));

    }

}

