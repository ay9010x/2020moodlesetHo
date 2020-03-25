<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


class tool_generator_make_testplan_form extends moodleform {

    
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('select', 'size', get_string('size', 'tool_generator'),
            tool_generator_testplan_backend::get_size_choices());
        $mform->setDefault('size', tool_generator_testplan_backend::DEFAULT_SIZE);

        $mform->addElement('select', 'courseid', get_string('targetcourse', 'tool_generator'),
            tool_generator_testplan_backend::get_course_options());

        $mform->addElement('advcheckbox', 'updateuserspassword', get_string('updateuserspassword', 'tool_generator'));
        $mform->addHelpButton('updateuserspassword', 'updateuserspassword', 'tool_generator');

        $mform->addElement('submit', 'submit', get_string('createtestplan', 'tool_generator'));
    }

    
    public function validation($data, $files) {
        global $CFG;

        $errors = array();
        if (empty($CFG->tool_generator_users_password) || is_bool($CFG->tool_generator_users_password)) {
            $errors['updateuserspassword'] = get_string('error_nouserspassword', 'tool_generator');
        }

                if ($courseerrors = tool_generator_testplan_backend::has_selected_course_any_problem($data['courseid'], $data['size'])) {
            $errors = array_merge($errors, $courseerrors);
        }

        return $errors;
    }

}
