<?php


defined('MOODLE_INTERNAL') || die();


class mod_feedback_course_select_form extends moodleform {
    
    protected $action;
    
    protected $feedbackstructure;

    
    public function __construct($action, mod_feedback_structure $feedbackstructure, $editable = true) {
        $this->action = new moodle_url($action, ['courseid' => null]);
        $this->feedbackstructure = $feedbackstructure;
        parent::__construct($action, null, 'post', '', ['id' => 'feedback_course_filter'], $editable);
    }

    
    public function definition() {
        $mform = $this->_form;
        $feedbackstructure = $this->feedbackstructure;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if (!$this->_form->_freezeAll && ($courses = $feedbackstructure->get_completed_courses()) && count($courses) > 1) {
            $elements = [];
            $elements[] = $mform->createElement('autocomplete', 'courseid', get_string('filter_by_course', 'feedback'),
                ['' => get_string('fulllistofcourses')] + $courses);
            $elements[] = $mform->createElement('submit', 'submitbutton', get_string('filter'));
            if ($feedbackstructure->get_courseid()) {
                $elements[] = $mform->createElement('static', 'showall', '',
                    html_writer::link($this->action, get_string('show_all', 'feedback')));
            }
            if (defined('BEHAT_SITE_RUNNING')) {
                                foreach ($elements as $element) {
                    $mform->addElement($element);
                }
            } else {
                $mform->addGroup($elements, 'coursefilter', get_string('filter_by_course', 'feedback'), array(' '), false);
            }
        }

        $this->set_data(['courseid' => $feedbackstructure->get_courseid(), 'id' => $feedbackstructure->get_cm()->id]);
    }
}
