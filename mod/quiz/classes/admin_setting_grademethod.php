<?php




defined('MOODLE_INTERNAL') || die();



class mod_quiz_admin_setting_grademethod extends admin_setting_configselect_with_advanced {
    public function load_choices() {
        global $CFG;

        if (is_array($this->choices)) {
            return true;
        }

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $this->choices = quiz_get_grading_options();

        return true;
    }
}
