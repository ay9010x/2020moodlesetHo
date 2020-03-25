<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/imscp/backup/moodle2/restore_imscp_stepslib.php');


class restore_imscp_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_imscp_activity_structure_step('imscp_structure', 'imscp.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('imscp', array('intro'), 'imscp');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('IMSCPVIEWBYID', '/mod/imscp/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('IMSCPINDEX', '/mod/imscp/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('imscp', 'add', 'view.php?id={course_module}', '{imscp}');
        $rules[] = new restore_log_rule('imscp', 'update', 'view.php?id={course_module}', '{imscp}');
        $rules[] = new restore_log_rule('imscp', 'view', 'view.php?id={course_module}', '{imscp}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('imscp', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
