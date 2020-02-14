<?php


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';

class grade_export_form extends moodleform {
    function definition() {
        global $CFG, $COURSE, $USER, $DB;

        $isdeprecatedui = false;

        $mform =& $this->_form;
        if (isset($this->_customdata)) {              $features = $this->_customdata;
        } else {
            $features = array();
        }

        if (empty($features['simpleui'])) {
            debugging('Grade export plugin needs updating to support one step exports.', DEBUG_DEVELOPER);
        }

        $mform->addElement('header', 'gradeitems', get_string('gradeitemsinc', 'grades'));
        $mform->setExpanded('gradeitems', true);

        if (!empty($features['idnumberrequired'])) {
            $mform->addElement('static', 'idnumberwarning', get_string('useridnumberwarning', 'grades'));
        }

        $switch = grade_get_setting($COURSE->id, 'aggregationposition', $CFG->grade_aggregationposition);

                $gseq = new grade_seq($COURSE->id, $switch);

        if ($grade_items = $gseq->items) {
            $needs_multiselect = false;
            $canviewhidden = has_capability('moodle/grade:viewhidden', context_course::instance($COURSE->id));

            foreach ($grade_items as $grade_item) {
                                if ($grade_item->is_hidden() && !$canviewhidden) {
                    continue;
                }

                if (!empty($features['idnumberrequired']) and empty($grade_item->idnumber)) {
                    $mform->addElement('checkbox', 'itemids['.$grade_item->id.']', $grade_item->get_name(), get_string('noidnumber', 'grades'));
                    $mform->hardFreeze('itemids['.$grade_item->id.']');
                } else {
                    $mform->addElement('advcheckbox', 'itemids['.$grade_item->id.']', $grade_item->get_name(), null, array('group' => 1));
                    $mform->setDefault('itemids['.$grade_item->id.']', 1);
                    $needs_multiselect = true;
                }
            }

            if ($needs_multiselect) {
                $this->add_checkbox_controller(1, null, null, 1);             }
        }


        $mform->addElement('header', 'options', get_string('exportformatoptions', 'grades'));
        if (!empty($features['simpleui'])) {
            $mform->setExpanded('options', false);
        }

        $mform->addElement('advcheckbox', 'export_feedback', get_string('exportfeedback', 'grades'));
        $mform->setDefault('export_feedback', 0);
        $coursecontext = context_course::instance($COURSE->id);
        if (has_capability('moodle/course:viewsuspendedusers', $coursecontext)) {
            $mform->addElement('advcheckbox', 'export_onlyactive', get_string('exportonlyactive', 'grades'));
            $mform->setType('export_onlyactive', PARAM_BOOL);
            $mform->setDefault('export_onlyactive', 1);
            $mform->addHelpButton('export_onlyactive', 'exportonlyactive', 'grades');
        } else {
            $mform->addElement('hidden', 'export_onlyactive', 1);
            $mform->setType('export_onlyactive', PARAM_BOOL);
            $mform->setConstant('export_onlyactive', 1);
        }

        if (empty($features['simpleui'])) {
            $options = array('10'=>10, '20'=>20, '100'=>100, '1000'=>1000, '100000'=>100000);
            $mform->addElement('select', 'previewrows', get_string('previewrows', 'grades'), $options);
        }



        if (!empty($features['updategradesonly'])) {
            $mform->addElement('advcheckbox', 'updatedgradesonly', get_string('updatedgradesonly', 'grades'));
        }
                        $options = array(GRADE_DISPLAY_TYPE_REAL       => get_string('real', 'grades'),
                         GRADE_DISPLAY_TYPE_PERCENTAGE => get_string('percentage', 'grades'),
                         GRADE_DISPLAY_TYPE_LETTER     => get_string('letter', 'grades'));

        
        if ($features['multipledisplaytypes']) {
            
            $checkboxes = array();
            $checkboxes[] = $mform->createElement('advcheckbox', 'display[real]', null, get_string('real', 'grades'), null, array(0, GRADE_DISPLAY_TYPE_REAL));
            $checkboxes[] = $mform->createElement('advcheckbox', 'display[percentage]', null, get_string('percentage', 'grades'), null, array(0, GRADE_DISPLAY_TYPE_PERCENTAGE));
            $checkboxes[] = $mform->createElement('advcheckbox', 'display[letter]', null, get_string('letter', 'grades'), null, array(0, GRADE_DISPLAY_TYPE_LETTER));
            $mform->addGroup($checkboxes, 'displaytypes', get_string('gradeexportdisplaytypes', 'grades'), ' ', false);
            $mform->setDefault('display[real]', $CFG->grade_export_displaytype == GRADE_DISPLAY_TYPE_REAL);
            $mform->setDefault('display[percentage]', $CFG->grade_export_displaytype == GRADE_DISPLAY_TYPE_PERCENTAGE);
            $mform->setDefault('display[letter]', $CFG->grade_export_displaytype == GRADE_DISPLAY_TYPE_LETTER);
        } else {
                        $mform->addElement('select', 'display', get_string('gradeexportdisplaytype', 'grades'), $options);
            $mform->setDefault('display', $CFG->grade_export_displaytype);
        }

                $options = array(0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
        $mform->addElement('select', 'decimals', get_string('gradeexportdecimalpoints', 'grades'), $options);
        $mform->setDefault('decimals', $CFG->grade_export_decimalpoints);
        $mform->disabledIf('decimals', 'display', 'eq', GRADE_DISPLAY_TYPE_LETTER);
        

        if (!empty($features['includeseparator'])) {
            $radio = array();
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('septab', 'grades'), 'tab');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcomma', 'grades'), 'comma');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepcolon', 'grades'), 'colon');
            $radio[] = $mform->createElement('radio', 'separator', null, get_string('sepsemicolon', 'grades'), 'semicolon');
            $mform->addGroup($radio, 'separator', get_string('separator', 'grades'), ' ', false);
            $mform->setDefault('separator', 'comma');
        }

        if (!empty($CFG->gradepublishing) and !empty($features['publishing'])) {
            $mform->addElement('header', 'publishing', get_string('publishingoptions', 'grades'));
            if (!empty($features['simpleui'])) {
                $mform->setExpanded('publishing', false);
            }
            $options = array(get_string('nopublish', 'grades'), get_string('createnewkey', 'userkey'));
            $keys = $DB->get_records_select('user_private_key', "script='grade/export' AND instance=? AND userid=?",
                            array($COURSE->id, $USER->id));
            if ($keys) {
                foreach ($keys as $key) {
                    $options[$key->value] = $key->value;                 }
            }
            $mform->addElement('select', 'key', get_string('userkey', 'userkey'), $options);
            $mform->addHelpButton('key', 'userkey', 'userkey');
            $mform->addElement('static', 'keymanagerlink', get_string('keymanager', 'userkey'),
                    '<a href="'.$CFG->wwwroot.'/grade/export/keymanager.php?id='.$COURSE->id.'">'.get_string('keymanager', 'userkey').'</a>');

            $mform->addElement('text', 'iprestriction', get_string('keyiprestriction', 'userkey'), array('size'=>80));
            $mform->addHelpButton('iprestriction', 'keyiprestriction', 'userkey');
            $mform->setDefault('iprestriction', getremoteaddr());             $mform->setType('iprestriction', PARAM_RAW_TRIMMED);

            $mform->addElement('date_time_selector', 'validuntil', get_string('keyvaliduntil', 'userkey'), array('optional'=>true));
            $mform->addHelpButton('validuntil', 'keyvaliduntil', 'userkey');
            $mform->setDefault('validuntil', time()+3600*24*7);             $mform->setType('validuntil', PARAM_INT);

            $mform->disabledIf('iprestriction', 'key', 'noteq', 1);
            $mform->disabledIf('validuntil', 'key', 'noteq', 1);
        }

        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);
        $submitstring = get_string('download');
        if (empty($features['simpleui'])) {
            $submitstring = get_string('submit');
        } else if (!empty($CFG->gradepublishing)) {
            $submitstring = get_string('export', 'grades');
        }

        $this->add_action_buttons(false, $submitstring);
    }

    
    public function get_data() {
        global $CFG;
        $data = parent::get_data();
        if ($data && $this->_customdata['multipledisplaytypes']) {
            if (count(array_filter($data->display)) == 0) {
                                if ($CFG->grade_export_displaytype == GRADE_DISPLAY_TYPE_LETTER) {
                    $data->display['letter'] = GRADE_DISPLAY_TYPE_LETTER;
                } else if ($CFG->grade_export_displaytype == GRADE_DISPLAY_TYPE_PERCENTAGE) {
                    $data->display['percentage'] = GRADE_DISPLAY_TYPE_PERCENTAGE;
                } else {
                    $data->display['real'] = GRADE_DISPLAY_TYPE_REAL;
                }
            }
        }
        return $data;
    }
}

