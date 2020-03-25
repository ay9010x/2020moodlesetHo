<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/feedback/file/locallib.php');


class assignfeedback_file_batch_upload_files_form extends moodleform {
    
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;
        $params = $this->_customdata;

        $mform->addElement('header', 'batchuploadfilesforusers', get_string('batchuploadfilesforusers', 'assignfeedback_file',
            count($params['users'])));
        $mform->addElement('static', 'userslist', get_string('selectedusers', 'assignfeedback_file'), $params['usershtml']);

        $data = new stdClass();
        $fileoptions = array('subdirs'=>1,
                                'maxbytes'=>$COURSE->maxbytes,
                                'accepted_types'=>'*',
                                'return_types'=>FILE_INTERNAL);

        $data = file_prepare_standard_filemanager($data,
                                                  'files',
                                                  $fileoptions,
                                                  $params['context'],
                                                  'assignfeedback_file',
                                                  ASSIGNFEEDBACK_FILE_BATCH_FILEAREA, $USER->id);

        $mform->addElement('filemanager', 'files_filemanager', '', null, $fileoptions);

        $this->set_data($data);

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'operation', 'plugingradingbatchoperation_file_uploadfiles');
        $mform->setType('operation', PARAM_ALPHAEXT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'uploadfiles');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'file');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $mform->addElement('hidden', 'selectedusers', implode(',', $params['users']));
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $this->add_action_buttons(true, get_string('uploadfiles', 'assignfeedback_file'));

    }

}

