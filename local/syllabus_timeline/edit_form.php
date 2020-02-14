<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class local_syllabus_timeline_edit_info_form extends moodleform {
    protected $course;
    protected $context;
    
    function definition() {
        global $CFG, $PAGE;

        $mform         = $this->_form;
        $returnto      = $this->_customdata['returnto'];
        $timelines         = $this->_customdata['data'];
        $course        = $this->_customdata['course'];
        $numsections   = $this->_customdata['numsections'];
        $coursecontext = context_course::instance($course->id);
        $this->context = $coursecontext;

        $mform->addElement('hidden', 'id', $course->id); 
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);
        
        for($w=0; $w<=$numsections;$w++){
            $sectionname = get_string('section', 'local_syllabus_timeline', $w);
            $mform->addElement('header', $sectionname, $sectionname);
            $mform->setExpanded($sectionname);
           
            $mform->addElement('text','topic'.$w, get_string('topic','local_syllabus_timeline'),'maxlength="254" size="50"');
            $mform->setType('topic'.$w, PARAM_TEXT);
            
            $mform->addElement('textarea', 'outline'.$w, get_string('outline','local_syllabus_timeline'), array('rows'=>3, 'cols'=>60));
            $mform->setType('outline'.$w, PARAM_TEXT);
        
            $mform->addElement('text','talk'.$w, get_string('talk', 'local_syllabus_timeline'), 'maxlength="3"  size="5"');
            $mform->addRule('talk'.$w, get_string('talk_rule','local_syllabus_timeline'), 'numeric', null, 'client');
            $mform->setType('talk'.$w, PARAM_INT);
            
            $mform->addElement('text','demo'.$w, get_string('demo', 'local_syllabus_timeline'), 'maxlength="3"  size="5"');
            $mform->addRule('demo'.$w, get_string('talk_rule','local_syllabus_timeline'), 'numeric', null, 'client');
            $mform->setType('demo'.$w, PARAM_INT);
            
            $mform->addElement('text','homework'.$w, get_string('homework', 'local_syllabus_timeline'), 'maxlength="3"  size="5"');
            $mform->addRule('homework'.$w, get_string('talk_rule','local_syllabus_timeline'), 'numeric', null, 'client');
            $mform->setType('homework'.$w, PARAM_INT);
            
            $mform->addElement('text','other'.$w, get_string('other', 'local_syllabus_timeline'), 'maxlength="3"  size="5"');
            $mform->addRule('other'.$w, get_string('talk_rule','local_syllabus_timeline'), 'numeric', null, 'client');
            $mform->setType('other'.$w, PARAM_INT);
            
            $mform->addElement('textarea', 'remark'.$w, get_string('remark','local_syllabus_timeline'), array('rows'=>2, 'cols'=>60));
            $mform->setType('remark'.$w, PARAM_TEXT);

        }
        $this->add_action_buttons();

        $this->set_data($timelines);
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