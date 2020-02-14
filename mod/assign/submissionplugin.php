<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/assignmentplugin.php');


abstract class assign_submission_plugin extends assign_plugin {

    
    public final function get_subtype() {
        return 'assignsubmission';
    }

    
    public function allow_submissions() {
        return true;
    }


    
    public function precheck_submission($submission) {
        return true;
    }

    
    public function submit_for_grading($submission) {
    }

    
    public function copy_submission( stdClass $oldsubmission, stdClass $submission) {
        return true;
    }

    
    public function lock($submission, stdClass $flags) {
    }

    
    public function unlock($submission, stdClass $flags) {
    }

    
    public function revert_to_draft(stdClass $submission) {
    }

    
    public function add_attempt(stdClass $oldsubmission, stdClass $newsubmission) {
    }

}
