<?php

require_once($CFG->libdir.'/formslib.php');

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }


class gradeimport_direct_import_form extends moodleform {

    
    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        if (isset($this->_customdata)) {              $features = $this->_customdata;
        } else {
            $features = array();
        }

                $mform->addElement('hidden', 'id', optional_param('id', 0, PARAM_INT));
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'general', get_string('pluginname', 'gradeimport_direct'));
                $mform->addElement('textarea', 'userdata', 'Data', array('rows' => 10, 'class' => 'gradeimport_data_area'));
        $mform->addRule('userdata', null, 'required');
        $mform->setType('userdata', PARAM_RAW);

        $encodings = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $encodings);
        $mform->addHelpButton('encoding', 'encoding', 'grades');

        if (!empty($features['verbosescales'])) {
            $options = array(1 => get_string('yes'), 0 => get_string('no'));
            $mform->addElement('select', 'verbosescales', get_string('verbosescales', 'grades'), $options);
            $mform->addHelpButton('verbosescales', 'verbosescales', 'grades');
        }

        $options = array('10' => 10, '20' => 20, '100' => 100, '1000' => 1000, '100000' => 100000);
        $mform->addElement('select', 'previewrows', get_string('rowpreviewnum', 'grades'), $options);
        $mform->addHelpButton('previewrows', 'rowpreviewnum', 'grades');
        $mform->setType('previewrows', PARAM_INT);
        $mform->addElement('hidden', 'groupid', groups_get_course_group($COURSE));
        $mform->setType('groupid', PARAM_INT);
        $mform->addElement('advcheckbox', 'forceimport', get_string('forceimport', 'grades'));
        $mform->addHelpButton('forceimport', 'forceimport', 'grades');
        $mform->setDefault('forceimport', false);
        $this->add_action_buttons(false, get_string('uploadgrades', 'grades'));
    }
}
