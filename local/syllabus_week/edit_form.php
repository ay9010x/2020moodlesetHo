<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class local_syllabus_week_edit_info_form extends moodleform {
    protected $course;
    protected $context;
    
    function definition() {
        global $CFG, $PAGE;

        $mform         = $this->_form;
        $returnto      = $this->_customdata['returnto'];
        $weeks         = $this->_customdata['data'];
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
            $sectionname = get_string('sectionheader', 'local_syllabus_week', $w);
            $mform->addElement('header', $sectionname, $sectionname);
            $mform->setExpanded($sectionname);
           
            $mform->addElement('date_selector', 'date'.$w, get_string('date', 'local_syllabus_week'));
            
            $option = array();
            for($i=1 ; $i<=7 ; $i++){
                if($i == 1){
                    $option[$i] = get_string('monday', 'local_syllabus_week');
                }else if($i == 2){
                    $option[$i] = get_string('tuesday', 'local_syllabus_week');
                }else if($i == 3){
                    $option[$i] = get_string('wednesday', 'local_syllabus_week');
                }else if($i == 4){
                    $option[$i] = get_string('thursday', 'local_syllabus_week');
                }else if($i == 5){
                    $option[$i] = get_string('friday', 'local_syllabus_week');
                }else if($i == 6){
                    $option[$i] = get_string('saturday', 'local_syllabus_week');
                }else{
                    $option[$i] = get_string('sunday', 'local_syllabus_week');
                }
            }
            $mform->addElement('select','week'.$w, get_string('week','local_syllabus_week'), $option);
            
            $option = array();
            for($i=1 ; $i<=10 ; $i++){
                $option[$i] = $i;
            }
            $mform->addElement('select','session'.$w, get_string('session','local_syllabus_week'), $option);
            $mform->addElement('text','location'.$w, get_string('location','local_syllabus_week'),'maxlength="20" size="20"');
            $mform->setType('location'.$w, PARAM_TEXT);
            $mform->addElement('text','summary'.$w, get_string('summary','local_syllabus_week'),'maxlength="254" size="50"');
            $mform->setType('summary'.$w, PARAM_TEXT);
        }
        $this->add_action_buttons();

        $this->set_data($weeks);
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