<?php



namespace core\event;

defined('MOODLE_INTERNAL') || die();


class role_capabilities_updated extends base {
    
    protected $legacylogdata = null;

    
    protected function init() {
        $this->data['objecttable'] = 'role';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    
    public static function get_name() {
        return get_string('eventrolecapabilitiesupdated', 'role');
    }

    
    public function get_description() {
        return "The user with id '$this->userid' updated the capabilities for the role with id '$this->objectid'.";
    }

    
    public function get_url() {
        if ($this->contextlevel == CONTEXT_SYSTEM) {
            return new \moodle_url('/admin/roles/define.php', array('action' => 'view', 'roleid' => $this->objectid));
        } else {
            return new \moodle_url('/admin/roles/override.php', array('contextid' => $this->contextid,
                'roleid' => $this->objectid));
        }
    }

    
    public function set_legacy_logdata($legacylogdata) {
        $this->legacylogdata = $legacylogdata;
    }

    
    protected function get_legacy_logdata() {
        return $this->legacylogdata;
    }

    public static function get_objectid_mapping() {
        return array('db' => 'role', 'restore' => 'role');
    }
}
