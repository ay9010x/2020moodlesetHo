<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';

class edit_calculation_form extends moodleform {
    public $available;
    public $noidnumbers;

    function definition() {
        global $COURSE;

        $mform =& $this->_form;

        $itemid = $this->_customdata['itemid'];

        $this->available = grade_item::fetch_all(array('courseid'=>$COURSE->id));
        $this->noidnumbers = array();

                        foreach ($this->available as $item) {
            if (empty($item->idnumber)) {
                $this->noidnumbers[$item->id] = $item;
                unset($this->available[$item->id]);
            }
            if ($item->id == $itemid) {                 unset($this->available[$item->id]);
            }
        }

        $mform->addElement('header', 'general', get_string('gradeitem', 'grades'));
        $mform->addElement('static', 'itemname', get_string('itemname', 'grades'));
        $mform->addElement('textarea', 'calculation', get_string('calculation', 'grades'), 'cols="60" rows="5"');
        $mform->addHelpButton('calculation', 'calculation', 'grades');

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', 0);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'section', 0);
        $mform->setType('section', PARAM_ALPHA);
        $mform->setDefault('section', 'calculation');

        $gpr = $this->_customdata['gpr'];
        $gpr->add_mform_elements($mform);

        $this->add_action_buttons();
    }

    function definition_after_data() {
        global $CFG, $COURSE;

        $mform =& $this->_form;
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $mform =& $this->_form;

                if ($data['calculation'] != '') {
            $grade_item = grade_item::fetch(array('id'=>$data['id'], 'courseid'=>$data['courseid']));
            $calculation = calc_formula::unlocalize(stripslashes($data['calculation']));
            $result = $grade_item->validate_formula($calculation);
            if ($result !== true) {
                $errors['calculation'] = $result;
            }
        }

        return $errors;
    }

}

