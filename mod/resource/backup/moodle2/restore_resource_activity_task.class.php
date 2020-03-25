<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/resource/backup/moodle2/restore_resource_stepslib.php'); 

class restore_resource_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_resource_activity_structure_step('resource_structure', 'resource.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('resource', array('intro'), 'resource');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('RESOURCEVIEWBYID', '/mod/resource/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('RESOURCEINDEX', '/mod/resource/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('resource', 'add', 'view.php?id={course_module}', '{resource}');
        $rules[] = new restore_log_rule('resource', 'update', 'view.php?id={course_module}', '{resource}');
        $rules[] = new restore_log_rule('resource', 'view', 'view.php?id={course_module}', '{resource}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('resource', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
