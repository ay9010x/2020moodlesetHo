<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');


class tool_customlang_filter_form extends moodleform {

    function definition() {
        $mform = $this->_form;
        $current = $this->_customdata['current'];

        $mform->addElement('header', 'filtersettings', get_string('filter', 'tool_customlang'));

                $options = array();
        foreach (tool_customlang_utils::list_components() as $component => $normalized) {
            list($type, $plugin) = core_component::normalize_component($normalized);
            if ($type == 'core' and is_null($plugin)) {
                $plugin = 'moodle';
            }
            $options[$type][$normalized] = $component.'.php';
        }
        $mform->addElement('selectgroups', 'component', get_string('filtercomponent', 'tool_customlang'), $options,
                           array('multiple'=>'multiple', 'size'=>7));

                $mform->addElement('advcheckbox', 'customized', get_string('filtercustomized', 'tool_customlang'));
        $mform->setType('customized', PARAM_BOOL);
        $mform->setDefault('customized', 0);

                $mform->addElement('advcheckbox', 'helps', get_string('filteronlyhelps', 'tool_customlang'));
        $mform->setType('helps', PARAM_BOOL);
        $mform->setDefault('helps', 0);

                $mform->addElement('advcheckbox', 'modified', get_string('filtermodified', 'tool_customlang'));
        $mform->setType('modified', PARAM_BOOL);
        $mform->setDefault('modified', 0);

                $mform->addElement('text', 'substring', get_string('filtersubstring', 'tool_customlang'));
        $mform->setType('substring', PARAM_RAW);

                $mform->addElement('text', 'stringid', get_string('filterstringid', 'tool_customlang'));
        $mform->setType('stringid', PARAM_STRINGID);

                $mform->addElement('submit', 'submit', get_string('filtershowstrings', 'tool_customlang'));
    }
}

