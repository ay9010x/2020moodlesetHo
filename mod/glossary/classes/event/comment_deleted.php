<?php



namespace mod_glossary\event;
defined('MOODLE_INTERNAL') || die();


class comment_deleted extends \core\event\comment_deleted {
    
    public function get_url() {
        return new \moodle_url('/mod/glossary/view.php', array('id' => $this->contextinstanceid));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the comment with id '$this->objectid' from the glossary activity " .
            "with course module id '$this->contextinstanceid'.";
    }
}
