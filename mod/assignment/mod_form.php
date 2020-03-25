<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot.'/course/moodleform_mod.php');


class mod_assignment_mod_form extends moodleform_mod {

    
    public function definition() {
        print_error('assignmentdisabled', 'assignment');
    }


}
