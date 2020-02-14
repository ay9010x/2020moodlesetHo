<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class blog_comment_created extends comment_created {

    
    public function get_url() {
        return new \moodle_url('/blog/index.php', array('entryid' => $this->other['itemid']));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' added the comment to the blog with id '{$this->other['itemid']}'.";
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['itemid'] = array('db' => 'post', 'restore' => base::NOT_MAPPED);
        return $othermapped;
    }
}
