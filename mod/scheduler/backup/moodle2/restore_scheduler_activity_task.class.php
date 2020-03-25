<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/scheduler/backup/moodle2/restore_scheduler_stepslib.php');


class restore_scheduler_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_scheduler_activity_structure_step('scheduler_structure', 'scheduler.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('scheduler', array('intro'), 'scheduler');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('SCHEDULERVIEWBYID', '/mod/scheduler/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SCHEDULERINDEX', '/mod/scheduler/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('scheduler', 'add', 'view.php?id={course_module}', '{scheduler}');
        $rules[] = new restore_log_rule('scheduler', 'update', 'view.php?id={course_module}', '{scheduler}');
        $rules[] = new restore_log_rule('scheduler', 'view', 'view.php?id={course_module}', '{scheduler}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('scheduler', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
