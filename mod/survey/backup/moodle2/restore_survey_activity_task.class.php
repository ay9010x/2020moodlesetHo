<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/survey/backup/moodle2/restore_survey_stepslib.php'); 

class restore_survey_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_survey_activity_structure_step('survey_structure', 'survey.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('survey', array('intro'), 'survey');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('SURVEYVIEWBYID', '/mod/survey/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SURVEYINDEX', '/mod/survey/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('survey', 'add', 'view.php?id={course_module}', '{survey}');
        $rules[] = new restore_log_rule('survey', 'update', 'view.php?id={course_module}', '{survey}');
        $rules[] = new restore_log_rule('survey', 'view', 'view.php?id={course_module}', '{survey}');
        $rules[] = new restore_log_rule('survey', 'download', 'download.php?id={course_module}&type=[type]&group=[group]', '{survey}');
        $rules[] = new restore_log_rule('survey', 'view report', 'report.php?id={course_module}', '{survey}');
        $rules[] = new restore_log_rule('survey', 'submit', 'view.php?id={course_module}', '{survey}');
        $rules[] = new restore_log_rule('survey', 'view graph', 'view.php?id={course_module}', '{survey}');
        $rules[] = new restore_log_rule('survey', 'view form', 'view.php?id={course_module}', '{survey}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('survey', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
