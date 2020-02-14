<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class comment_deleted extends \core\event\comment_deleted {

    
    public function get_url() {
        return new \moodle_url('/mod/wiki/comments.php', array('pageid' => $this->other['itemid']));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted a comment with id '$this->objectid' on the page with id " .
            "'{$this->other['itemid']}' for the wiki with course module id '$this->contextinstanceid'.";
    }
}
