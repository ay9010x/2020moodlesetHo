<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');


require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once('HTML/QuickForm/input.php');


class mod_assign_grade_form extends moodleform {
    
    private $assignment;

    
    public function definition() {
        $mform = $this->_form;

        list($assignment, $data, $params) = $this->_customdata;
                $this->assignment = $assignment;
        $assignment->add_grade_form_elements($mform, $data, $params);

        if ($data) {
            $this->set_data($data);
        }
    }

    
    protected function get_form_identifier() {
        $params = $this->_customdata[2];
        return get_class($this) . '_' . $params['userid'];
    }

    
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $instance = $this->assignment->get_instance();

        if ($instance->markingworkflow && !empty($data['sendstudentnotifications']) &&
                $data['workflowstate'] != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
            $errors['workflowstate'] = get_string('studentnotificationworkflowstateerror', 'assign');
        }

                if (!array_key_exists('grade', $data)) {
            return $errors;
        }

        if ($instance->grade > 0) {
            if (unformat_float($data['grade'], true) === false && (!empty($data['grade']))) {
                $errors['grade'] = get_string('invalidfloatforgrade', 'assign', $data['grade']);
            } else if (unformat_float($data['grade']) > $instance->grade) {
                $errors['grade'] = get_string('gradeabovemaximum', 'assign', $instance->grade);
            } else if (unformat_float($data['grade']) < 0) {
                $errors['grade'] = get_string('gradebelowzero', 'assign');
            }
        } else {
                        if ($scale = $DB->get_record('scale', array('id'=>-($instance->grade)))) {
                $scaleoptions = make_menu_from_list($scale->scale);
                if ((int)$data['grade'] !== -1 && !array_key_exists((int)$data['grade'], $scaleoptions)) {
                    $errors['grade'] = get_string('invalidgradeforscale', 'assign');
                }
            }
        }
        return $errors;
    }
}
