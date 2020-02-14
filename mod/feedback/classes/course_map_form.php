<?php


defined('MOODLE_INTERNAL') || die();


class mod_feedback_course_map_form extends moodleform {
    
    public function definition() {
        $mform  = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $options = array('multiple' => true, 'includefrontpage' => true);
        $mform->addElement('course', 'mappedcourses', get_string('courses'), $options);

        $this->add_action_buttons();
    }
}
