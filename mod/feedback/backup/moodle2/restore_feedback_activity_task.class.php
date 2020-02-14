<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/feedback/backup/moodle2/restore_feedback_stepslib.php'); 

class restore_feedback_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_feedback_activity_structure_step('feedback_structure', 'feedback.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('feedback', array('intro', 'site_after_submit', 'page_after_submit'), 'feedback');
        $contents[] = new restore_decode_content('feedback_item', array('presentation'), 'feedback_item');
        $contents[] = new restore_decode_content('feedback_value', array('value'), 'feedback_value');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('FEEDBACKINDEX', '/mod/feedback/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('FEEDBACKVIEWBYID', '/mod/feedback/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('FEEDBACKANALYSISBYID', '/mod/feedback/analysis.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('FEEDBACKSHOWENTRIESBYID', '/mod/feedback/show_entries.php?id=$1', 'course_module');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('feedback', 'add', 'view.php?id={course_module}', '{feedback}');
        $rules[] = new restore_log_rule('feedback', 'update', 'view.php?id={course_module}', '{feedback}');
        $rules[] = new restore_log_rule('feedback', 'view', 'view.php?id={course_module}', '{feedback}');
        $rules[] = new restore_log_rule('feedback', 'submit', 'view.php?id={course_module}', '{feedback}');
        $rules[] = new restore_log_rule('feedback', 'startcomplete', 'view.php?id={course_module}', '{feedback}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('feedback', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
