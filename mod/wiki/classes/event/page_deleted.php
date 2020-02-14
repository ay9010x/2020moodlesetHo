<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class page_deleted extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wiki_pages';
    }

    
    public static function get_name() {
        return get_string('eventpagedeleted', 'mod_wiki');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' deleted the page with id '$this->objectid' for the wiki with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return(array($this->courseid, 'wiki', 'admin', 'admin.php?pageid=' . $this->objectid, $this->objectid,
            $this->contextinstanceid));
    }

    
    public function get_url() {
        return new \moodle_url('/mod/wiki/admin.php', array('pageid' => $this->objectid));
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki_pages', 'restore' => 'wiki_page');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['subwikiid'] = array('db' => 'wiki_subwikis', 'restore' => 'wiki_subwiki');

        return $othermapped;
    }
}
