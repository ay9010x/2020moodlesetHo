<?php



defined('MOODLE_INTERNAL') || die();



class enrol_category_plugin extends enrol_plugin {

   
    public function can_delete_instance($instance) {
        global $DB;

        $context = context_course::instance($instance->courseid);
        if (!has_capability('enrol/category:config', $context)) {
            return false;
        }

        if (!enrol_is_enabled('category')) {
            return true;
        }
                return !$DB->record_exists('user_enrolments', array('enrolid'=>$instance->id));
    }

    
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/category:config', $context);
    }

    
    public function get_newinstance_link($courseid) {
                return null;
    }

    
    public function cron() {
        global $CFG;

        if (!enrol_is_enabled('category')) {
            return;
        }

        require_once("$CFG->dirroot/enrol/category/locallib.php");
        $trace = new null_progress_trace();
        enrol_category_sync_full($trace);
    }

    
    public function course_updated($inserted, $course, $data) {
        global $CFG;

        if (!enrol_is_enabled('category')) {
            return;
        }

                require_once("$CFG->dirroot/enrol/category/locallib.php");
        enrol_category_sync_course($course);
    }

    
    public function restore_sync_course($course) {
        global $CFG;
        require_once("$CFG->dirroot/enrol/category/locallib.php");
        enrol_category_sync_course($course);
    }
}
