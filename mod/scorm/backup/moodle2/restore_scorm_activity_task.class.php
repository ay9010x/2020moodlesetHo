<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/scorm/backup/moodle2/restore_scorm_stepslib.php'); 

class restore_scorm_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_scorm_activity_structure_step('scorm_structure', 'scorm.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('scorm', array('intro'), 'scorm');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('SCORMVIEWBYID', '/mod/scorm/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SCORMINDEX', '/mod/scorm/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('scorm', 'add', 'view.php?id={course_module}', '{scorm}');
        $rules[] = new restore_log_rule('scorm', 'update', 'view.php?id={course_module}', '{scorm}');
        $rules[] = new restore_log_rule('scorm', 'view', 'player.php?cm={course_module}&scoid={scorm_sco}', '{scorm}');
        $rules[] = new restore_log_rule('scorm', 'pre-view', 'view.php?id={course_module}', '{scorm}');
        $rules[] = new restore_log_rule('scorm', 'report', 'report.php?id={course_module}', '{scorm}');
        $rules[] = new restore_log_rule('scorm', 'launch', 'view.php?id={course_module}', '[result]');
        $rules[] = new restore_log_rule('scorm', 'delete attempts', 'report.php?id={course_module}', '[oldattempts]');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('scorm', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
