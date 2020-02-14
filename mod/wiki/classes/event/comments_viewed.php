<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class comments_viewed extends \core\event\comments_viewed {

    
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'wiki_pages';
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the comments for the page with id '$this->objectid' for the wiki " .
            "with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return(array($this->courseid, 'wiki', 'comments',
            'comments.php?pageid=' . $this->objectid, $this->objectid, $this->contextinstanceid));
    }

    
    public function get_url() {
        return new \moodle_url('/mod/wiki/comments.php', array('pageid' => $this->objectid));
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki_pages', 'restore' => 'wiki_page');
    }
}
