<?php


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';
require_once($CFG->libdir.'/gradelib.php');

class grade_import_form extends moodleform {
    function definition (){
        global $COURSE;

        $mform =& $this->_form;

        if (isset($this->_customdata)) {              $features = $this->_customdata;
        } else {
            $features = array();
        }

                $mform->addElement('hidden', 'id', optional_param('id', 0, PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('header', 'general', get_string('importfile', 'grades'));

                if (!empty($features['acceptedtypes'])) {
            $acceptedtypes = $features['acceptedtypes'];
        } else {
            $acceptedtypes = '*';
        }

                $mform->addElement('filepicker', 'userfile', get_string('file'), null, array('accepted_types' => $acceptedtypes));
        $mform->addRule('userfile', null, 'required');
        $encodings = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'grades'), $encodings);
        $mform->addHelpButton('encoding', 'encoding', 'grades');

        if (!empty($features['includeseparator'])) {
            $radio = array();
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcolon', 'grades'), 'colon');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepsemicolon', 'grades'), 'semicolon');
            $mform->addGroup($radio, 'separator', get_string('separator', 'grades'), ' ', false);
            $mform->addHelpButton('separator', 'separator', 'grades');
            $mform->setDefault('separator', 'comma');
        }

        if (!empty($features['verbosescales'])) {
            $options = array(1=>get_string('yes'), 0=>get_string('no'));
            $mform->addElement('select', 'verbosescales', get_string('verbosescales', 'grades'), $options);
            $mform->addHelpButton('verbosescales', 'verbosescales', 'grades');
        }

        $options = array('10'=>10, '20'=>20, '100'=>100, '1000'=>1000, '100000'=>100000);
        $mform->addElement('select', 'previewrows', get_string('rowpreviewnum', 'grades'), $options);         $mform->addHelpButton('previewrows', 'rowpreviewnum', 'grades');
        $mform->setType('previewrows', PARAM_INT);
        $mform->addElement('checkbox', 'forceimport', get_string('forceimport', 'grades'));
        $mform->addHelpButton('forceimport', 'forceimport', 'grades');
        $mform->setDefault('forceimport', false);
        $mform->setType('forceimport', PARAM_BOOL);
        $mform->addElement('hidden', 'groupid', groups_get_course_group($COURSE));
        $mform->setType('groupid', PARAM_INT);
        $this->add_action_buttons(false, get_string('uploadgrades', 'grades'));
    }
}

class grade_import_mapping_form extends moodleform {

    function definition () {
        global $CFG, $COURSE;
        $mform =& $this->_form;

                $header = $this->_customdata['header'];
        
        $mform->addElement('header', 'general', get_string('identifier', 'grades'));
        $mapfromoptions = array();

        if ($header) {
            foreach ($header as $i=>$h) {
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
            $i = 0;             foreach ($header as $h) {
                $h = trim($h);
                                $headermapsto = array(
                    get_string('others', 'grades')     => array(
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
        $mform->setConstant('verbosescales', $this->_customdata['verbosescales']);
        $mform->addElement('hidden', 'groupid', groups_get_course_group($COURSE));
        $mform->setType('groupid', PARAM_INT);
        $mform->setConstant('groupid', groups_get_course_group($COURSE));
        $mform->addElement('hidden', 'forceimport', $this->_customdata['forceimport']);
        $mform->setType('forceimport', PARAM_BOOL);
        $mform->setConstant('forceimport', $this->_customdata['forceimport']);
        $this->add_action_buttons(false, get_string('uploadgrades', 'grades'));

    }
}
