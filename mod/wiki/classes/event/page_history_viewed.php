<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class page_history_viewed extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wiki_pages';
    }

    
    public static function get_name() {
        return get_string('eventhistoryviewed', 'mod_wiki');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the history for the page with id '$this->objectid' for the wiki with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return(array($this->courseid, 'wiki', 'history',
            'history.php?pageid=' . $this->objectid, $this->objectid, $this->contextinstanceid));
    }

    
    public function get_url() {
        return new \moodle_url('/mod/wiki/history.php', array('pageid' => $this->objectid));
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki_pages', 'restore' => 'wiki_page');
    }
}
