<?php



namespace assignsubmission_comments\event;
defined('MOODLE_INTERNAL') || die();


class comment_created extends \core\event\comment_created {
    
    public function get_url() {
        return new \moodle_url('/mod/assign/view.php', array('id' => $this->contextinstanceid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' added the comment with id '$this->objectid' to the submission " .
            "with id '{$this->other['itemid']}' for the assignment with course module id '$this->contextinstanceid'.";
    }
}
