<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/backup/moodle2/restore_assign_stepslib.php');


class restore_assign_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_assign_activity_structure_step('assign_structure', 'assign.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('assign', array('intro'), 'assign');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('ASSIGNVIEWBYID',
                                           '/mod/assign/view.php?id=$1',
                                           'course_module');
        $rules[] = new restore_decode_rule('ASSIGNINDEX',
                                           '/mod/assign/index.php?id=$1',
                                           'course_module');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('assign', 'add', 'view.php?id={course_module}', '{assign}');
        $rules[] = new restore_log_rule('assign', 'update', 'view.php?id={course_module}', '{assign}');
        $rules[] = new restore_log_rule('assign', 'view', 'view.php?id={course_module}', '{assign}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        return $rules;
    }

    
    public function get_comment_mapping_itemname($commentarea) {
        switch ($commentarea) {
            case 'submission_comments':
                $itemname = 'submission';
                break;
            default:
                $itemname = parent::get_comment_mapping_itemname($commentarea);
                break;
        }

        return $itemname;
    }
}
