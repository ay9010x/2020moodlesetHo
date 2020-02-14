<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/url/backup/moodle2/restore_url_stepslib.php'); 

class restore_url_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_url_activity_structure_step('url_structure', 'url.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('url', array('intro', 'externalurl'), 'url');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('URLINDEX', '/mod/url/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('URLVIEWBYID', '/mod/url/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('URLVIEWBYU', '/mod/url/view.php?u=$1', 'url');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('url', 'add', 'view.php?id={course_module}', '{url}');
        $rules[] = new restore_log_rule('url', 'update', 'view.php?id={course_module}', '{url}');
        $rules[] = new restore_log_rule('url', 'view', 'view.php?id={course_module}', '{url}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('url', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
