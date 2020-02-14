<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir."/formslib.php");


class tinymce_managefiles_manage_form extends moodleform {
    function definition() {
        global $PAGE;
        $mform = $this->_form;

        $itemid           = $this->_customdata['draftitemid'];
        $options          = $this->_customdata['options'];
        $files            = $this->_customdata['files'];

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);
        $mform->addElement('hidden', 'maxbytes');
        $mform->setType('maxbytes', PARAM_INT);
        $mform->addElement('hidden', 'subdirs');
        $mform->setType('subdirs', PARAM_INT);
        $mform->addElement('hidden', 'accepted_types');
        $mform->setType('accepted_types', PARAM_RAW);
        $mform->addElement('hidden', 'return_types');
        $mform->setType('return_types', PARAM_INT);
        $mform->addElement('hidden', 'context');
        $mform->setType('context', PARAM_INT);
        $mform->addElement('hidden', 'areamaxbytes');
        $mform->setType('areamaxbytes', PARAM_INT);

        $mform->addElement('filemanager', 'files_filemanager', '', null, $options);

        $mform->addElement('submit', 'refresh', get_string('refreshfiles', 'tinymce_managefiles'));
        $mform->registerNoSubmitButton('refresh');

        $mform->addElement('static', '', '',
                html_writer::tag('div', '', array('class' => 'managefilesstatus')));

        $mform->addElement('header', 'deletefiles', get_string('unusedfilesheader', 'tinymce_managefiles'));
        $mform->addElement('static', '', '',
                html_writer::tag('span', get_string('unusedfilesdesc', 'tinymce_managefiles'), array('class' => 'managefilesunuseddesc')));
        foreach ($files as $file) {
            $mform->addElement('checkbox', 'deletefile['.$file.']', '', $file);
            $mform->setType('deletefile['.$file.']', PARAM_INT);
        }
        $mform->addElement('submit', 'delete', get_string('deleteselected', 'tinymce_managefiles'));

        $PAGE->requires->js_init_call('M.tinymce_managefiles.analysefiles', array(), true);
        $PAGE->requires->strings_for_js(array('allfilesok', 'hasmissingfiles'), 'tinymce_managefiles');

        $this->set_data(array('files_filemanager' => $itemid,
            'itemid' => $itemid,
            'subdirs' => $options['subdirs'],
            'maxbytes' => $options['maxbytes'],
            'areamaxbytes' => $options['areamaxbytes'],
            'accepted_types' => $options['accepted_types'],
            'return_types' => $options['return_types'],
            'context' => $options['context']->id,
            ));
    }
}
