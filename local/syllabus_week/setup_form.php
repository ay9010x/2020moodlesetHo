<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class local_syllabus_week_setup_edit_info_form extends moodleform {
    protected $course;
    protected $context;
    
    function definition() {
        global $CFG, $PAGE;

        $mform         = $this->_form;
        $returnto      = $this->_customdata['returnto'];
        $data          = $this->_customdata['data'];
        $course        = $this->_customdata['course'];
        $coursecontext = context_course::instance($course->id);
        $this->context = $coursecontext;

        $mform->addElement('hidden', 'id', $course->id); 
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);
        
        
        $mform->addElement('header', 'descriptionhdr', get_string('course_info', 'local_mooccourse'));
                
        $mform->addElement('selectyesno', 'date', get_string('showdate','local_syllabus_week'));
        $mform->addElement('selectyesno', 'week', get_string('showweek','local_syllabus_week'));
        $mform->addElement('selectyesno', 'session', get_string('showsession','local_syllabus_week'));
        $mform->addElement('selectyesno', 'location', get_string('showlocation','local_syllabus_week'));
        
        $this->add_action_buttons();

        $this->set_data($data);
    }

    function definition_after_data() {
        global $DB;

        $mform = $this->_form;
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        
        return $errors;
    }
}