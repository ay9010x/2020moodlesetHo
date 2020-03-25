<?php



namespace core\event;
defined('MOODLE_INTERNAL') || die();


class blog_comment_deleted extends comment_deleted {

    
    public function get_url() {
        return new \moodle_url('/blog/index.php', array('entryid' => $this->other['itemid']));
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the comment for the blog with id '{$this->other['itemid']}'.";
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['itemid'] = array('db' => 'post', 'restore' => base::NOT_MAPPED);
        return $othermapped;
    }
}
