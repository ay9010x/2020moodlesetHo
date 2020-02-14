<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/choice/backup/moodle2/restore_choice_stepslib.php'); 

class restore_choice_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_choice_activity_structure_step('choice_structure', 'choice.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('choice', array('intro'), 'choice');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('CHOICEVIEWBYID', '/mod/choice/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('CHOICEINDEX', '/mod/choice/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('choice', 'add', 'view.php?id={course_module}', '{choice}');
        $rules[] = new restore_log_rule('choice', 'update', 'view.php?id={course_module}', '{choice}');
        $rules[] = new restore_log_rule('choice', 'view', 'view.php?id={course_module}', '{choice}');
        $rules[] = new restore_log_rule('choice', 'choose', 'view.php?id={course_module}', '{choice}');
        $rules[] = new restore_log_rule('choice', 'choose again', 'view.php?id={course_module}', '{choice}');
        $rules[] = new restore_log_rule('choice', 'report', 'report.php?id={course_module}', '{choice}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

                $rules[] = new restore_log_rule('choice', 'view all', 'index?id={course}', null,
                                        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('choice', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
