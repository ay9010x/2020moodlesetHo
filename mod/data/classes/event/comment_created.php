<?php



namespace mod_data\event;
defined('MOODLE_INTERNAL') || die();


class comment_created extends \core\event\comment_created {
    
    public function get_url() {
        return new \moodle_url('/mod/data/view.php', array('id' => $this->contextinstanceid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' added the comment with id '$this->objectid' to the database activity with " .
            "course module id '$this->contextinstanceid'.";
    }
}
