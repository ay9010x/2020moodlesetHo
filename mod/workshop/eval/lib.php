<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');


abstract class workshop_evaluation {

    
    abstract public function update_grading_grades(stdClass $settings, $restrict=null);

    
    public function get_settings_form(moodle_url $actionurl=null) {

        $customdata = array('workshop' => $this->workshop);
        $attributes = array('class' => 'evalsettingsform');

        return new workshop_evaluation_settings_form($actionurl, $customdata, 'post', '', $attributes);
    }

    
    public static function delete_instance($workshopid) {

    }
}



class workshop_evaluation_settings_form extends moodleform {

    
    public function definition() {
        $mform = $this->_form;

        $workshop = $this->_customdata['workshop'];

        $mform->addElement('header', 'general', get_string('evaluationsettings', 'mod_workshop'));

        $this->definition_sub();

        $mform->addElement('submit', 'submit', get_string('aggregategrades', 'workshop'));
    }

    
    protected function definition_sub() {
    }
}
