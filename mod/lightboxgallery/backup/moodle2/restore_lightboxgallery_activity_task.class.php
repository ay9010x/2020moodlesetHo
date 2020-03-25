<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lightboxgallery/backup/moodle2/restore_lightboxgallery_stepslib.php');


class restore_lightboxgallery_activity_task extends restore_activity_task {

    
    protected function define_my_settings() {
            }

    
    protected function define_my_steps() {
                $this->add_step(new restore_lightboxgallery_activity_structure_step('lightboxgallery_structure', 'lightboxgallery.xml'));
    }

    
    static public function define_decode_contents() {
        $contents = array();
        return $contents;
    }

    
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('LIGHTBOXGALLERYVIEWBYID', '/mod/lightboxgallery/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('LIGHTBOXGALLERYINDEX', '/mod/lightboxgallery/index.php?id=$1', 'course');

        return $rules;

    }

    
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('lightboxgallery', 'add', 'view.php?id={course_module}', '{page}');
        $rules[] = new restore_log_rule('lightboxgallery', 'update', 'view.php?id={course_module}', '{page}');
        $rules[] = new restore_log_rule('lightboxgallery', 'view', 'view.php?id={course_module}', '{page}');

        return $rules;
    }

    
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('lightboxgallery', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
