<?php



namespace mod_glossary\event;
defined('MOODLE_INTERNAL') || die();


class entry_created extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'glossary_entries';
    }

    
    public static function get_name() {
        return get_string('evententrycreated', 'mod_glossary');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has created the glossary entry with id '$this->objectid' for " .
            "the glossary activity with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
        return new \moodle_url("/mod/glossary/view.php",
                array('id' => $this->contextinstanceid,
                    'mode' => 'entry',
                    'hook' => $this->objectid));
    }

    
    public function get_legacy_logdata() {
        return array($this->courseid, 'glossary', 'add entry',
            "view.php?id={$this->contextinstanceid}&amp;mode=entry&amp;hook={$this->objectid}",
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

    public static function get_other_mapping() {
                return false;
    }
}

