<?php



if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once $CFG->libdir.'/formslib.php';

class edit_scale_form extends moodleform {
    function definition() {
        global $CFG;
        $mform =& $this->_form;

                $mform->addElement('header', 'general', get_string('scale'));

        $mform->addElement('text', 'name', get_string('name'), 'size="40"');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'standard', get_string('scalestandard'));
        $mform->addHelpButton('standard', 'scalestandard');

        $mform->addElement('static', 'used', get_string('used'));

        $mform->addElement('textarea', 'scale', get_string('scale'), array('cols'=>50, 'rows'=>2));
        $mform->addHelpButton('scale', 'scale');
        $mform->addRule('scale', get_string('required'), 'required', null, 'client');
        $mform->setType('scale', PARAM_TEXT);

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

        $courseid = $mform->getElementValue('courseid');

        if ($id = $mform->getElementValue('id')) {
            $scale = grade_scale::fetch(array('id'=>$id));
            $used = $scale->is_used();

            if ($used) {
                $mform->hardFreeze('scale');
            }

            if (empty($courseid)) {
                $mform->hardFreeze('standard');

            } else if (!has_capability('moodle/course:managescales', context_system::instance())) {
                                $mform->hardFreeze('standard');

            } else if ($used and !empty($scale->courseid)) {
                $mform->hardFreeze('standard');
            }

            $usedstr = $scale->is_used() ? get_string('yes') : get_string('no');
            $used_el =& $mform->getElement('used');
            $used_el->setValue($usedstr);

        } else {
            $mform->removeElement('used');
            if (empty($courseid) or !has_capability('moodle/course:managescales', context_system::instance())) {
                $mform->hardFreeze('standard');
            }
        }
    }

    function validation($data, $files) {
        global $CFG, $COURSE, $DB;

        $errors = parent::validation($data, $files);

                
        $old = grade_scale::fetch(array('id'=>$data['id']));

        if (array_key_exists('standard', $data)) {
            if (empty($data['standard'])) {
                $courseid = $COURSE->id;
            } else {
                $courseid = 0;
            }

        } else {
            $courseid = $old->courseid;
        }

        if (array_key_exists('scale', $data)) {
            $scalearray = explode(',', $data['scale']);
            $scalearray = array_map('trim', $scalearray);
            $scaleoptioncount = count($scalearray);

            if (count($scalearray) < 1) {
                $errors['scale'] = get_string('badlyformattedscale', 'grades');
            } else {
                $thescale = implode(',',$scalearray);

                                $count = $DB->count_records_select('scale', "courseid=:courseid AND ".$DB->sql_compare_text('scale', core_text::strlen($thescale)).'=:scale',
                    array('courseid'=>$courseid, 'scale'=>$thescale));

                if ($count) {
                                                            if (empty($old->id) or $old->courseid != $courseid) {
                        $errors['scale'] = get_string('duplicatescale', 'grades');
                    } else if ($old->scale !== $thescale and $old->scale !== $data['scale']) {
                                                $errors['scale'] = get_string('duplicatescale', 'grades');
                    }
                }
            }
        }

        return $errors;
    }
}


