<?php



namespace mod_wiki\event;
defined('MOODLE_INTERNAL') || die();


class page_viewed extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'wiki_pages';
    }

    
    public static function get_name() {
        return get_string('eventpageviewed', 'mod_wiki');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' viewed the page with id '$this->objectid' for the wiki with " .
            "course module id '$this->contextinstanceid'.";
    }

    
    protected function get_legacy_logdata() {
        if (!empty($this->other['wid'])) {
            return(array($this->courseid, 'wiki', 'view',
                'view.php?wid=' . $this->data['other']['wid'] . '&title=' . $this->data['other']['title'],
                $this->data['other']['wid'], $this->contextinstanceid));
        } else if (!empty($this->other['prettyview'])) {
            return(array($this->courseid, 'wiki', 'view',
                'prettyview.php?pageid=' . $this->objectid, $this->objectid, $this->contextinstanceid));
        } else {
            return(array($this->courseid, 'wiki', 'view',
                'view.php?pageid=' . $this->objectid, $this->objectid, $this->contextinstanceid));
        }
    }

    
    public function get_url() {
        if (!empty($this->data['other']['wid'])) {
            return new \moodle_url('/mod/wiki/view.php', array('wid' => $this->data['other']['wid'],
                    'title' => $this->data['other']['title'],
                    'uid' => $this->relateduserid,
                    'groupanduser' => $this->data['other']['groupanduser'],
                    'group' => $this->data['other']['group']
                ));
        } else if (!empty($this->other['prettyview'])) {
            return new \moodle_url('/mod/wiki/prettyview.php', array('pageid' => $this->objectid));
        } else {
            return new \moodle_url('/mod/wiki/view.php', array('pageid' => $this->objectid));
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'wiki_pages', 'restore' => 'wiki_page');
    }

    public static function get_other_mapping() {
        $othermapped = array();
        $othermapped['wid'] = array('db' => 'wiki', 'restore' => 'wiki');
        $othermapped['group'] = array('db' => 'groups', 'restore' => 'group');

        return $othermapped;
    }
}
