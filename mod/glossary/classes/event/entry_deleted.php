<?php



namespace mod_glossary\event;
defined('MOODLE_INTERNAL') || die();


class entry_deleted extends \core\event\base {
    
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'glossary_entries';
    }

    
    public static function get_name() {
        return get_string('evententrydeleted', 'mod_glossary');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' has deleted the glossary entry with id '$this->objectid' in " .
            "the glossary activity with course module id '$this->contextinstanceid'.";
    }

    
    public function get_url() {
                $params = array('id' => $this->contextinstanceid);
        if (isset($this->other['hook'])) {
            $params['hook'] = $this->other['hook'];
        }
        if (isset($this->other['mode'])) {
            $params['mode'] = $this->other['mode'];
        }
        return new \moodle_url("/mod/glossary/view.php", $params);
    }

    
    public function get_legacy_logdata() {
        $hook = $mode = '';
        if (isset($this->other['hook'])) {
            $hook = $this->other['hook'];
        }
        if (isset($this->other['mode'])) {
            $mode = $this->other['mode'];
        }
        return array($this->courseid, 'glossary', 'delete entry',
            "view.php?id={$this->contextinstanceid}&amp;mode={$mode}&amp;hook={$hook}",
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

