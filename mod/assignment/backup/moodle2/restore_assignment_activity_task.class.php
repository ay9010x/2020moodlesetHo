<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assignment/backup/moodle2/restore_assignment_stepslib.php'); 

class restore_assignment_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_assignment_activity_structure_step('assignment_structure', 'assignment.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('assignment', array('intro'), 'assignment');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('ASSIGNMENTVIEWBYID', '/mod/assignment/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('ASSIGNMENTINDEX', '/mod/assignment/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('assignment', 'add', 'view.php?id={course_module}', '{assignment}');
        $rules[] = new restore_log_rule('assignment', 'update', 'view.php?id={course_module}', '{assignment}');
        $rules[] = new restore_log_rule('assignment', 'view', 'view.php?id={course_module}', '{assignment}');
        $rules[] = new restore_log_rule('assignment', 'upload', 'view.php?a={assignment}', '{assignment}');
        $rules[] = new restore_log_rule('assignment', 'view submission', 'submissions.php.php?id={course_module}', '{assignment}');
        $rules[] = new restore_log_rule('assignment', 'update grades', 'submissions.php.php?id={course_module}&user={user}', '{user}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('assignment', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    
    public function get_mode() {
        return $this->plan->get_mode();
    }
}
