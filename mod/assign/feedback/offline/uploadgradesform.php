<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');


class assignfeedback_offline_upload_grades_form extends moodleform {
    
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;
        $params = $this->_customdata;

        $mform->addElement('header', 'uploadgrades', get_string('uploadgrades', 'assignfeedback_offline'));

        $fileoptions = array('subdirs'=>0,
                                'maxbytes'=>$COURSE->maxbytes,
                                'accepted_types'=>'csv',
                                'maxfiles'=>1,
                                'return_types'=>FILE_INTERNAL);

        $mform->addElement('filepicker', 'gradesfile', get_string('uploadafile'), null, $fileoptions);
        $mform->addRule('gradesfile', get_string('uploadnofilefound'), 'required', null, 'client');
        $mform->addHelpButton('gradesfile', 'gradesfile', 'assignfeedback_offline');

        $encodings = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $encodings);
        $mform->addHelpButton('encoding', 'encoding', 'grades');

        $radio = array();
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcolon', 'grades'), 'colon');
        $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepsemicolon', 'grades'), 'semicolon');
        $mform->addGroup($radio, 'separator', get_string('separator', 'grades'), ' ', false);
        $mform->addHelpButton('separator', 'separator', 'grades');
        $mform->setDefault('separator', 'comma');

        $mform->addElement('checkbox', 'ignoremodified', '', get_string('ignoremodified', 'assignfeedback_offline'));
        $mform->addHelpButton('ignoremodified', 'ignoremodified', 'assignfeedback_offline');

        $mform->addElement('hidden', 'id', $params['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'viewpluginpage');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'pluginaction', 'uploadgrades');
        $mform->setType('pluginaction', PARAM_ALPHA);
        $mform->addElement('hidden', 'plugin', 'offline');
        $mform->setType('plugin', PARAM_PLUGIN);
        $mform->addElement('hidden', 'pluginsubtype', 'assignfeedback');
        $mform->setType('pluginsubtype', PARAM_PLUGIN);
        $this->add_action_buttons(true, get_string('uploadgrades', 'assignfeedback_offline'));

    }

}

