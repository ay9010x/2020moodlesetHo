<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class page_version_restored extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wiki_versions';
    }

    
    public static function get_name() {
        return get_string('eventversionrestored', 'mod_wiki');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' restored version '$this->objectid' for the page with id '{$this->other['pageid']}' " .
            "for the wiki with course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return(array($this->courseid, 'wiki', 'restore',
            'view.php?pageid=' . $this->other['pageid'], $this->other['pageid'], $this->contextinstanceid));
    }

    
    public function get_url() {
        return new \moodle_url('/mod/wiki/viewversion.php', array('pageid' => $this->other['pageid'],
            'versionid' => $this->objectid));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['pageid'])) {
            throw new \coding_exception('The pageid needs to be set in $other');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki_versions', 'restore' => 'wiki_version');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['pageid'] = array('db' => 'wiki_pages', 'restore' => 'wiki_page');

        return $othermapped;
    }
}
