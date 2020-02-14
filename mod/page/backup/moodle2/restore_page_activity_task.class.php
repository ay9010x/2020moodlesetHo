<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/page/backup/moodle2/restore_page_stepslib.php'); 

class restore_page_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_page_activity_structure_step('page_structure', 'page.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('page', array('intro', 'content'), 'page');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('PAGEVIEWBYID', '/mod/page/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('PAGEINDEX', '/mod/page/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('page', 'add', 'view.php?id={course_module}', '{page}');
        $rules[] = new restore_log_rule('page', 'update', 'view.php?id={course_module}', '{page}');
        $rules[] = new restore_log_rule('page', 'view', 'view.php?id={course_module}', '{page}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('page', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
