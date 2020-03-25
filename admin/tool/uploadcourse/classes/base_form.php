<?php



defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');


class tool_uploadcourse_base_form extends moodleform {

    
    public function definition() {
    }

    
    public function add_import_options() {
        $mform = $this->_form;

                $mform->addElement('header', 'importoptionshdr', get_string('importoptions', 'tool_uploadcourse'));
        $mform->setExpanded('importoptionshdr', true);

        $choices = array(
            tool_uploadcourse_processor::MODE_CREATE_NEW => get_string('createnew', 'tool_uploadcourse'),
            tool_uploadcourse_processor::MODE_CREATE_ALL => get_string('createall', 'tool_uploadcourse'),
            tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE => get_string('createorupdate', 'tool_uploadcourse'),
            tool_uploadcourse_processor::MODE_UPDATE_ONLY => get_string('updateonly', 'tool_uploadcourse')
        );
        $mform->addElement('select', 'options[mode]', get_string('mode', 'tool_uploadcourse'), $choices);
        $mform->addHelpButton('options[mode]', 'mode', 'tool_uploadcourse');

        $choices = array(
            tool_uploadcourse_processor::UPDATE_NOTHING => get_string('nochanges', 'tool_uploadcourse'),
            tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_ONLY => get_string('updatewithdataonly', 'tool_uploadcourse'),
            tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_OR_DEFAUTLS =>
                get_string('updatewithdataordefaults', 'tool_uploadcourse'),
            tool_uploadcourse_processor::UPDATE_MISSING_WITH_DATA_OR_DEFAUTLS => get_string('updatemissing', 'tool_uploadcourse')
        );
        $mform->addElement('select', 'options[updatemode]', get_string('updatemode', 'tool_uploadcourse'), $choices);
        $mform->setDefault('options[updatemode]', tool_uploadcourse_processor::UPDATE_NOTHING);
        $mform->disabledIf('options[updatemode]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[updatemode]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[updatemode]', 'updatemode', 'tool_uploadcourse');

        $mform->addElement('selectyesno', 'options[allowdeletes]', get_string('allowdeletes', 'tool_uploadcourse'));
        $mform->setDefault('options[allowdeletes]', 0);
        $mform->disabledIf('options[allowdeletes]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[allowdeletes]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[allowdeletes]', 'allowdeletes', 'tool_uploadcourse');

        $mform->addElement('selectyesno', 'options[allowrenames]', get_string('allowrenames', 'tool_uploadcourse'));
        $mform->setDefault('options[allowrenames]', 0);
        $mform->disabledIf('options[allowrenames]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[allowrenames]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[allowrenames]', 'allowrenames', 'tool_uploadcourse');

        $mform->addElement('selectyesno', 'options[allowresets]', get_string('allowresets', 'tool_uploadcourse'));
        $mform->setDefault('options[allowresets]', 0);
        $mform->disabledIf('options[allowresets]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[allowresets]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[allowresets]', 'allowresets', 'tool_uploadcourse');
    }

}
