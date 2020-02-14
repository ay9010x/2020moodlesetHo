<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';

class edit_outcome_form extends moodleform {
    public function definition() {
        global $CFG, $COURSE;
        $mform =& $this->_form;

                $mform->addElement('header', 'general', get_string('outcomes', 'grades'));

        $mform->addElement('text', 'fullname', get_string('outcomefullname', 'grades'), 'size="40"');
        $mform->addRule('fullname', get_string('required'), 'required');
        $mform->setType('fullname', PARAM_TEXT);

        $mform->addElement('text', 'shortname', get_string('outcomeshortname', 'grades'), 'size="20"');
        $mform->addRule('shortname', get_string('required'), 'required');
        $mform->setType('shortname', PARAM_NOTAGS);

        $mform->addElement('advcheckbox', 'standard', get_string('outcomestandard', 'grades'));
        $mform->addHelpButton('standard', 'outcomestandard', 'grades');

        $options = array();

        $mform->addElement('selectwithlink', 'scaleid', get_string('scale'), $options, null,
            array('link' => $CFG->wwwroot.'/grade/edit/scale/edit.php?courseid='.$COURSE->id, 'label' => get_string('scalescustomcreate')));
        $mform->addHelpButton('scaleid', 'typescale', 'grades');
        $mform->addRule('scaleid', get_string('required'), 'required');

        $mform->addElement('editor', 'description_editor', get_string('description'), null, $this->_customdata['editoroptions']);


                $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', 0);
        $mform->setType('courseid', PARAM_INT);

        $gpr = $this->_customdata['gpr'];
        $gpr->add_mform_elements($mform);

                $this->add_action_buttons();
    }


    function definition_after_data() {
        global $CFG;

        $mform =& $this->_form;

                if ($courseid = $mform->getElementValue('courseid')) {
            $options = array();
            if ($scales = grade_scale::fetch_all_local($courseid)) {
                $options[-1] = '--'.get_string('scalescustom');
                foreach($scales as $scale) {
                    $options[$scale->id] = $scale->get_name();
                }
            }
            if ($scales = grade_scale::fetch_all_global()) {
                $options[-2] = '--'.get_string('scalesstandard');
                foreach($scales as $scale) {
                    $options[$scale->id] = $scale->get_name();
                }
            }
            $scale_el =& $mform->getElement('scaleid');
            $scale_el->load($options);

        } else {
            $options = array();
            if ($scales = grade_scale::fetch_all_global()) {
                foreach($scales as $scale) {
                    $options[$scale->id] = $scale->get_name();
                }
            }
            $scale_el =& $mform->getElement('scaleid');
            $scale_el->load($options);
        }

        if ($id = $mform->getElementValue('id')) {
            $outcome = grade_outcome::fetch(array('id'=>$id));
            $itemcount   = $outcome->get_item_uses_count();
            $coursecount = $outcome->get_course_uses_count();

            if ($itemcount) {
                $mform->hardFreeze('scaleid');
            }

            if (empty($courseid)) {
                $mform->hardFreeze('standard');

            } else if (!has_capability('moodle/grade:manage', context_system::instance())) {
                $mform->hardFreeze('standard');

            } else if ($coursecount and empty($outcome->courseid)) {
                $mform->hardFreeze('standard');
            }


        } else {
            if (empty($courseid) or !has_capability('moodle/grade:manage', context_system::instance())) {
                $mform->hardFreeze('standard');
            }
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['scaleid'] < 1) {
            $errors['scaleid'] = get_string('required');
        }

        if (!empty($data['standard']) and $scale = grade_scale::fetch(array('id'=>$data['scaleid']))) {
            if (!empty($scale->courseid)) {
                                $errors['scaleid'] = 'Can not use custom scale in global outcome!';
            }
        }

        return $errors;
    }


}


