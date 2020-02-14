<?php


namespace core\event;

defined('MOODLE_INTERNAL') || die();


class competency_comment_deleted extends \core\event\comment_deleted {

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the comment with id '$this->objectid' from '$this->component'";
    }
}
