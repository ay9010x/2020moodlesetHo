<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class page_map_viewed extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wiki_pages';
    }

    
    public static function get_name() {
        return get_string('eventmapviewed', 'mod_wiki');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the wiki map for the page with id '$this->objectid' for the wiki with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return(array($this->courseid, 'wiki', 'map',
            'map.php?pageid=' . $this->objectid, $this->objectid, $this->contextinstanceid));
    }

    
    public function get_url() {
        return new \moodle_url('/mod/wiki/map.php', array('pageid' => $this->objectid));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['option'])) {
            throw new \coding_exception('The \'option\' value must be set in other, even if 0.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki_pages', 'restore' => 'wiki_page');
    }

    public static function get_other_mapping() {
                return false;
    }
}
