<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/label/backup/moodle2/restore_label_stepslib.php'); 

class restore_label_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_label_activity_structure_step('label_structure', 'label.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('label', array('intro'), 'label');

        return $contents;
    }

    
    static public function define_decode_rules() {
        return array();
    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('label', 'add', 'view.php?id={course_module}', '{label}');
        $rules[] = new restore_log_rule('label', 'update', 'view.php?id={course_module}', '{label}');
        $rules[] = new restore_log_rule('label', 'view', 'view.php?id={course_module}', '{label}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('label', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
