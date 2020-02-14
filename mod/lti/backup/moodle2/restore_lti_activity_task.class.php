<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/backup/moodle2/restore_lti_stepslib.php');


class restore_lti_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_lti_activity_structure_step('lti_structure', 'lti.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('lti', array('intro'), 'lti');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('LTIVIEWBYID', '/mod/lti/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LTIINDEX', '/mod/lti/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('lti', 'add', 'view.php?id={course_module}', '{lti}');
        $rules[] = new restore_log_rule('lti', 'update', 'view.php?id={course_module}', '{lti}');
        $rules[] = new restore_log_rule('lti', 'view', 'view.php?id={course_module}', '{lti}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('lti', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    
    public function get_old_moduleid() {
        return $this->oldmoduleid;
    }
}
