<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/folder/backup/moodle2/restore_folder_stepslib.php'); 

class restore_folder_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_folder_activity_structure_step('folder_structure', 'folder.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('folder', array('intro'), 'folder');

        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('FOLDERVIEWBYID', '/mod/folder/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('FOLDERINDEX', '/mod/folder/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('folder', 'add', 'view.php?id={course_module}', '{folder}');
        $rules[] = new restore_log_rule('folder', 'edit', 'edit.php?id={course_module}', '{folder}');
        $rules[] = new restore_log_rule('folder', 'view', 'view.php?id={course_module}', '{folder}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('folder', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
