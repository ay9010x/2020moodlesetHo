<?php



namespace mod_glossary\event;
defined('MOODLE_INTERNAL') || die();


class entry_viewed extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'glossary_entries';
    }

    
    public static function get_name() {
        return get_string('evententryviewed', 'mod_glossary');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has viewed the glossary entry with id '$this->objectid' in " .
            "the glossary activity with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url("/mod/glossary/showentry.php",
                array('eid' => $this->objectid));
    }

    
    public function get_legacy_logdata() {
        return array($this->courseid, 'glossary', 'view entry',
            "showentry.php?eid={$this->objectid}",
            $this->objectid, $this->contextinstanceid);
    }

    
    protected function validate_data() {
        parent::validate_data();
                if (!$this->contextlevel === CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }

    public static function get_objectid_mapping() {
        return array('db' => 'glossary_entries', 'restore' => 'glossary_entry');
    }
}

