<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class page_version_viewed extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wiki_pages';
    }

    
    public static function get_name() {
        return get_string('eventversionviewed', 'mod_wiki');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the version for the page with id '$this->objectid' for the wiki with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        return(array($this->courseid, 'wiki', 'history',
            'viewversion.php?pageid=' . $this->objectid . '&versionid=' . $this->other['versionid'],
            $this->objectid, $this->contextinstanceid));
    }

    
    public function get_url() {
        return new \moodle_url('/mod/wiki/viewversion.php', array('pageid' => $this->objectid,
            'versionid' => $this->other['versionid']));
    }

    
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['versionid'])) {
            throw new \coding_exception('The versionid need to be set in $other');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki_pages', 'restore' => 'wiki_page');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['versionid'] = array('db' => 'wiki_versions', 'restore' => 'wiki_version');

        return $othermapped;
    }
}
