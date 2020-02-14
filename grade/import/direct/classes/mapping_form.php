<?php

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/gradelib.php');

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }


class gradeimport_direct_mapping_form extends moodleform {

    
    public function definition() {
        global $CFG, $COURSE;
        $mform = $this->_form;

                $header = $this->_customdata['header'];
        
        $mform->addElement('header', 'general', get_string('identifier', 'grades'));
        $mapfromoptions = array();

        if ($header) {
            foreach ($header as $i => $h) {
                $mapfromoptions[$i] = s($h);
            }
        }
        $mform->addElement('select', 'mapfrom', get_string('mapfrom', 'grades'), $mapfromoptions);
        $mform->addHelpButton('mapfrom', 'mapfrom', 'grades');

        $maptooptions = array(
            'userid'       => get_string('userid', 'grades'),
            'username'     => get_string('username'),
            'useridnumber' => get_string('idnumber'),
            'useremail'    => get_string('email'),
            '0'            => get_string('ignore', 'grades')
        );
        $mform->addElement('select', 'mapto', get_string('mapto', 'grades'), $maptooptions);
        $mform->addHelpButton('mapto', 'mapto', 'grades');

        $mform->addElement('header', 'general_map', get_string('mappings', 'grades'));
        $mform->addHelpButton('general_map', 'mappings', 'grades');

                $feedbacks = array();
        if ($gradeitems = $this->_customdata['gradeitems']) {
            foreach ($gradeitems as $itemid => $itemname) {
                $feedbacks['feedback_'.$itemid] = get_string('feedbackforgradeitems', 'grades', $itemname);
            }
        }

        if ($header) {
            $i = 0;
            foreach ($header as $h) {
                $h = trim($h);
                                $headermapsto = array(
                    get_string('others', 'grades') => array(
                        '0'   => get_string('ignore', 'grades'),
                        'new' => get_string('newitem', 'grades')
                    ),
                    get_string('gradeitems', 'grades') => $gradeitems,
                    get_string('feedbacks', 'grades')  => $feedbacks
                );
                $mform->addElement('selectgroups', 'mapping_'.$i, s($h), $headermapsto);
                $i++;
            }
        }
                $mform->addElement('hidden', 'map', 1);
        $mform->setType('map', PARAM_INT);
        $mform->setConstant('map', 1);
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $this->_customdata['id']);
        $mform->addElement('hidden', 'iid', $this->_customdata['iid']);
        $mform->setType('iid', PARAM_INT);
        $mform->setConstant('iid', $this->_customdata['iid']);
        $mform->addElement('hidden', 'importcode', $this->_customdata['importcode']);
        $mform->setType('importcode', PARAM_FILE);
        $mform->setConstant('importcode', $this->_customdata['importcode']);
        $mform->addElement('hidden', 'verbosescales', 1);
        $mform->setType('verbosescales', PARAM_INT);
        $mform->setConstant('verbosescales', $this->_customdata['importcode']);
        $mform->addElement('hidden', 'groupid', groups_get_course_group($COURSE));
        $mform->setType('groupid', PARAM_INT);
        $mform->setConstant('groupid', groups_get_course_group($COURSE));
        $mform->addElement('hidden', 'forceimport', $this->_customdata['forceimport']);
        $mform->setType('forceimport', PARAM_BOOL);
        $mform->setConstant('forceimport', $this->_customdata['forceimport']);
        $this->add_action_buttons(false, get_string('uploadgrades', 'grades'));
    }
}
