<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');


class tool_installaddon_installfromzip_form extends moodleform {

    
    public function definition() {

        $mform = $this->_form;
        $installer = $this->_customdata['installer'];

        $mform->addElement('header', 'general', get_string('installfromzip', 'tool_installaddon'));
        $mform->addHelpButton('general', 'installfromzip', 'tool_installaddon');

        $mform->addElement('filepicker', 'zipfile', get_string('installfromzipfile', 'tool_installaddon'),
            null, array('accepted_types' => '.zip'));
        $mform->addHelpButton('zipfile', 'installfromzipfile', 'tool_installaddon');
        $mform->addRule('zipfile', null, 'required', null, 'client');

        $options = $installer->get_plugin_types_menu();
        $mform->addElement('select', 'plugintype', get_string('installfromziptype', 'tool_installaddon'), $options,
            array('id' => 'tool_installaddon_installfromzip_plugintype'));
        $mform->addHelpButton('plugintype', 'installfromziptype', 'tool_installaddon');
        $mform->setAdvanced('plugintype');

        $mform->addElement('static', 'permcheck', '',
            html_writer::span(get_string('permcheck', 'tool_installaddon'), '',
                array('id' => 'tool_installaddon_installfromzip_permcheck')));
        $mform->setAdvanced('permcheck');

        $mform->addElement('text', 'rootdir', get_string('installfromziprootdir', 'tool_installaddon'));
        $mform->addHelpButton('rootdir', 'installfromziprootdir', 'tool_installaddon');
        $mform->setType('rootdir', PARAM_PLUGIN);
        $mform->setAdvanced('rootdir');

        $this->add_action_buttons(false, get_string('installfromzipsubmit', 'tool_installaddon'));
    }

    
    public function require_explicit_plugintype() {

        $mform = $this->_form;

        $mform->addRule('plugintype', get_string('required'), 'required', null, 'client');
        $mform->setAdvanced('plugintype', false);
        $mform->setAdvanced('permcheck', false);

        $typedetectionfailed = $mform->createElement('static', 'typedetectionfailed', '',
            html_writer::span(get_string('typedetectionfailed', 'tool_installaddon'), 'error'));
        $mform->insertElementBefore($typedetectionfailed, 'permcheck');
    }

    
    public function selected_plugintype_mismatch($detected) {

        $mform = $this->_form;
        $mform->addRule('plugintype', get_string('required'), 'required', null, 'client');
        $mform->setAdvanced('plugintype', false);
        $mform->setAdvanced('permcheck', false);
        $mform->insertElementBefore($mform->createElement('static', 'selectedplugintypemismatch', '',
            html_writer::span(get_string('typedetectionmismatch', 'tool_installaddon', $detected), 'error')), 'permcheck');
    }

    
    public function validation($data, $files) {

        $pluginman = core_plugin_manager::instance();
        $errors = parent::validation($data, $files);

        if (!empty($data['plugintype'])) {
            if (!$pluginman->is_plugintype_writable($data['plugintype'])) {
                $path = $pluginman->get_plugintype_root($data['plugintype']);
                $errors['plugintype'] = get_string('permcheckresultno', 'tool_installaddon', array('path' => $path));
            }
        }

        return $errors;
    }
}
