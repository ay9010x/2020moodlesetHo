<?php


namespace core\event;

defined('MOODLE_INTERNAL') || die();


class competency_comment_created extends \core\event\comment_created {

    
    public function get_description() {
        return "The user with id '$this->userid' added the comment with id '$this->objectid' to the '$this->component'";
    }

    
    public function get_url() {
        return null;
    }
}
