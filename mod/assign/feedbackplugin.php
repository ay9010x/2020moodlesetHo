<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/assign/assignmentplugin.php');


abstract class assign_feedback_plugin extends assign_plugin {

    
    public function get_subtype() {
        return 'assignfeedback';
    }

    
    public function format_for_gradebook(stdClass $grade) {
        return FORMAT_MOODLE;
    }

    
    public function text_for_gradebook(stdClass $grade) {
        return '';
    }

    
    public function supports_quickgrading() {
        return false;
    }

    
    public function get_quickgrading_html($userid, $grade) {
        return false;
    }

    
    public function is_quickgrading_modified($userid, $grade) {
        return false;
    }

    
    public function is_feedback_modified(stdClass $grade, stdClass $data) {
        debugging('This plugin (' . $this->get_name() . ') has not overwritten the is_feedback_modified() method.
                Please add this method to your plugin', DEBUG_DEVELOPER);
        return true;
    }

    
    public function save_quickgrading_changes($userid, $grade) {
        return false;
    }

    
    public function get_grading_batch_operations() {
        return array();
    }

    
    public function get_grading_actions() {
        return array();
    }

    
    public function grading_action($gradingaction) {
        return '';
    }

    
    public function supports_review_panel() {
        return false;
    }

    
    public function grading_batch_operation($action, $users) {
        return '';
    }
}
